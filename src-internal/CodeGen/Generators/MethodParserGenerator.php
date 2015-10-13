<?php
namespace Recoil\Amqp\CodeGen\Generators;

use Recoil\Amqp\CodeGen\CodeGenerator;
use Recoil\Amqp\CodeGen\CodeGeneratorHelperTrait;
use Recoil\Amqp\Exception\ProtocolException;

final class MethodParserGenerator implements CodeGenerator
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
        yield 'FrameParserMethodTrait.php' => $this->generateTrait(
            $amqpVersion,
            $amqpSpec
        );
    }

    private function generateTrait($amqpVersion, $amqpSpec)
    {
        yield '<?php';
        yield 'namespace Recoil\Amqp\\' . $amqpVersion . '\Protocol;';
        yield;
        yield 'use ' . ProtocolException::class . ';';
        yield;
        yield 'trait FrameParserMethodTrait';
        yield '{';
        yield '    private function parseMethodFrame()';
        yield '    {';
        yield '        list(, $class, $method) = unpack("n2", $this->buffer);';
        yield '        ' . $this->generateAdvanceBufferCode(4);

        yield $this->generateDispatchCode($amqpSpec);

        yield;
        yield '        throw ProtocolException::create("Frame class (" . $class . ") is invalid.");';
        yield '    }';
        yield '}';
    }

    private function generateDispatchCode($amqpSpec)
    {
        $classElse = '';
        foreach ($amqpSpec->classes as $classIndex => $class) {
            yield;
            yield '        // class "' . $class->name . '"';
            yield '        ' . $classElse . 'if ($class === ' . $class->id . ') {';
            $classElse = '} else';

            $methodElse = '';
            foreach ($class->methods as $method) {
                if ($this->isIncomingMethod($method)) {
                    yield;
                    yield '            // method "' . $class->name . '.' . $method->name . '"';
                    yield '            ' . $methodElse . 'if ($method === ' . $method->id . ') {';
                    $methodElse = '} else';

                    yield $this->generateMethodParserCode($amqpSpec, $class, $method);
                }
            }

            yield '            }';
            yield;
            yield '            throw ProtocolException::create(';
            yield '                "Frame method (" . $method . ") is invalid for class \"' . $class->name . '\"."';
            yield '            );';
        }

        yield '        }';
    }

    private function generateMethodParserCode($amqpSpec, $class, $method)
    {
        $entityClass = $this->toBumpyCase($class->name) . '\\' . $this->toBumpyCase($class->name, $method->name) . 'Frame';

        if (!$method->arguments) {
            yield '                return new ' . $entityClass . '();';

            return;
        }

        $this->bitArgs = [];
        $this->fixedArgs = [];

        yield '                $frame = new ' . $entityClass . '();';

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

            yield;
            yield '                // consume "' . $argument->name . '" (' . $type . ')';

            if ($type === 'shortstr') {
                yield '                $frame->' . $this->toCamelCase($argument->name) . ' = $this->parseShortString();';
            } elseif ($type === 'longstr') {
                yield '                $frame->' . $this->toCamelCase($argument->name) . ' = $this->parseLongString();';
            } elseif ($type === 'table') {
                yield '                $frame->' . $this->toCamelCase($argument->name) . ' = $this->parseFieldTable();';
            } else {
                throw new RuntimeException('Unknown type: ' . $type . '.');
            }
        }

        yield $this->flushBitArgs();
        yield $this->flushFixedArgs($amqpSpec);

        yield;
        yield '                return $frame;';
    }

    private function flushBitArgs()
    {
        $arguments = $this->bitArgs;
        $this->bitArgs = [];

        if (1 === count($arguments)) {
            yield;
            yield '                // consume "' . $arguments[0]->name . '" (bit)';
            yield '                $frame->' . $this->toCamelCase($arguments[0]->name) . ' = ord($this->buffer) !== 0;';
            yield '                ' . $this->generateAdvanceBufferCode(1);
        } elseif ($arguments) {
            yield;

            foreach ($arguments as $argument) {
                yield '                // consume "' . $argument->name . '" (bit)';
            }

            foreach ($arguments as $index => $argument) {
                $shift = $index % 8;

                if (0 === $shift) {
                    yield '                $octet = ord($this->buffer);';
                    yield '                ' . $this->generateAdvanceBufferCode(1);
                }

                yield '                $frame->' . $this->toCamelCase($argument->name) . ' = $octet & ' . (1 << $shift) . ' !== 0;';
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

            yield;
            yield '                // consume "' . $name . '" (' . $type . ')';

            if ($format === 'C') {
                yield '                $frame->' . $name . ' = ord($this->buffer);';
            } else {
                yield '                list(, $frame->' . $name . ') = unpack(' . var_export($format, true) . ', $this->buffer);';
            }

            yield '                ' . $this->generateAdvanceBufferCode($size);
        } elseif ($arguments) {
            yield;

            $size    = 0;
            $format  = '';

            foreach ($arguments as $index => $argument) {
                $key = chr(ord('a') + $index);
                $type = $this->resolveArgumentType($amqpSpec, $argument);
                $size += $this->sizeInBytes($type);
                $format .= $this->packFormat($type) . $key . '/';

                yield '                // consume (' . $key . ') "' . $argument->name . '" (' . $type . ')';
            }

            yield '                $fields = unpack(' . var_export(rtrim($format, '/'), true) . ', $this->buffer);';
            yield '                ' . $this->generateAdvanceBufferCode($size);

            foreach ($arguments as $index => $argument) {
                $key = chr(ord('a') + $index);
                yield '                $frame->' . $this->toCamelCase($argument->name) . ' = $fields["' . $key . '"];';
            }
        }
    }

    use CodeGeneratorHelperTrait;

    private $bitArgs;
    private $fixedArgs;
}
