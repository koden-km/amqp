<?php
namespace Recoil\Amqp\CodeGen\Generators;

use Recoil\Amqp\CodeGen\CodeGenerator;
use Recoil\Amqp\CodeGen\CodeGeneratorHelperTrait;

final class MethodSerializerGenerator implements CodeGenerator
{
    /**
     * Generate code based on the AMQP specification.
     *
     * @param string $amqpVersion The AMQP version (e.g. "v091").
     * @param object $amqpSpec    The AMQP specification object.
     *
     * The return value is a map of target filename to content.
     *
     * If the content is a string it is written to the file, otherwise the
     * content is treated as a sequence of lines. Any non strings in the
     * sequence are traversed recursively.
     *
     * @return mixed<string, string|traversable> A map of filename to string content, or to sequence of lines.
     */
    public function generate($amqpVersion, $amqpSpec)
    {
        yield 'MethodSerializerTrait.php' => $this->generateTrait(
            $amqpVersion,
            $amqpSpec
        );
    }

    private function generateTrait($amqpVersion, $amqpSpec)
    {
        yield '<?php';
        yield 'namespace Recoil\Amqp\Protocol\\' . $amqpVersion . ';';
        yield;
        yield 'trait MethodSerializerTrait';
        yield '{';

        foreach ($amqpSpec->classes as $class) {
            foreach ($class->methods as $method) {
                if ($this->isOutgoingMethod($method)) {
                    $bumpyClass = $this->toBumpyCase($class->name);
                    $bumpyMethod = $this->toBumpyCase($method->name);

                    yield sprintf(
                        '    public function visit%s%sFrame(%s\\%sFrame $frame)',
                        $bumpyClass,
                        $bumpyMethod,
                        $bumpyClass,
                        $bumpyMethod
                    );

                    yield '    {';
                    yield $this->generateMethodSerializerCode($amqpSpec, $class, $method);
                    yield '    }';
                    yield;
                }
            }
        }

        yield '}';
    }

    private function generateMethodSerializerCode($amqpSpec, $class, $method)
    {
        $methodType = $this->generateLiteralBuffer(
            chr($this->getConstant($amqpSpec, 'FRAME-METHOD'))
        );

        if (!$method->arguments) {
            $buffer = pack(
                'NnnC',
                8, // payload size - only class/method id
                $class->id,
                $method->id,
                $this->getConstant($amqpSpec, 'FRAME-END')
            );

            yield '        return ' . $methodType . ' . pack("n", $frame->channel) . ' . $this->generateLiteralBuffer($buffer) . ';';

            return;
        }

        $this->bitArgs = [];
        $this->fixedArgs = [];

        yield '        $payload = ' . $this->generateLiteralBuffer(pack('nn', $class->id, $method->id));

        foreach ($method->arguments as $argument) {
            $type = $this->resolveArgumentType($amqpSpec, $argument);

            if ($type === 'bit') {
                $this->bitArgs[] = $argument;
                continue;
            } else {
                yield $this->flushBitArgs();
            }

            if ($this->isFixedSize($type)) {
                $this->fixedArgs[] = $argument;
                continue;
            } else {
                yield $this->flushFixedArgs($amqpSpec);
            }

            if ($type === 'shortstr') {
                yield '                 . $this->serializeShortString($frame->' . $this->toCamelCase($argument->name) . ')';
            } elseif ($type === 'longstr') {
                yield '                 . $this->serializeLongString($frame->' . $this->toCamelCase($argument->name) . ')';
            } elseif ($type === 'table') {
                yield '                 . $this->serializeTable($frame->' . $this->toCamelCase($argument->name) . ')';
            } else {
                throw new RuntimeException('Unknown type: ' . $type . '.');
            }
        }

        yield $this->flushBitArgs();
        yield $this->flushFixedArgs($amqpSpec);
        yield '                 ;';

        $frameEnd = $this->generateLiteralBuffer(
            chr($this->getConstant($amqpSpec, 'FRAME-END'))
        );

        yield;
        yield '        return ' . $methodType . ' . pack("nN", $frame->channel, strlen($payload)) . $payload . ' . $frameEnd . ';';
    }

    private function flushBitArgs()
    {
        $arguments = $this->bitArgs;
        $this->bitArgs = [];

        if (1 === count($arguments)) {
            yield '                 . ($frame->' . $this->toCamelCase($arguments[0]->name) . ' ? "\x01" : "\x00")';
        } elseif ($arguments) {
            foreach (array_chunk($arguments, 8) as $args) {
                yield '                 . chr(';

                foreach ($args as $index => $argument) {
                    if ($index === 0) {
                        yield '                       $frame->' . $this->toCamelCase($argument->name);
                    } else {
                        yield '                     | $frame->' . $this->toCamelCase($argument->name) . ' << ' . $index;
                    }
                }

                yield '                 )';
            }
        }
    }

    private function flushFixedArgs($amqpSpec)
    {
        $arguments = $this->fixedArgs;
        $this->fixedArgs = [];

        if (1 === count($arguments)) {
            $argument = $arguments[0];
            $type     = $this->resolveArgumentType($amqpSpec, $argument);
            $size     = $this->sizeInBytes($type);
            $format   = $this->packFormat($type);
            $name     = $this->toCamelCase($argument->name);

            if ($format === 'C') {
                yield '                 . chr($frame->' . $name . ')';
            } else {
                yield '                 . pack(' . var_export($format, true) . ', $frame->' . $name . ')';
            }
        } elseif ($arguments) {
            $size    = 0;
            $names   = [];
            $format  = [];
            $comment = [];

            foreach ($arguments as $argument) {
                $type = $this->resolveArgumentType($amqpSpec, $argument);
                $size += $this->sizeInBytes($type);
                $names[] = $this->toCamelCase($argument->name);
                $format[] = $this->packFormat($type);
            }

            $format = implode('', $format);
            $names = '$frame->' . implode(', $frame->', $names);

            yield '                 . pack(' . var_export($format, true) . ', ' . $names . ')';
        }
    }

    use CodeGeneratorHelperTrait;

    private $bitArgs;
    private $fixedArgs;
}
