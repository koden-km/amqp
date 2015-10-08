<?php
namespace Recoil\Amqp\CodeGen;

/**
 * @codeCoverageIgnore
 */
final class MethodSerializerTraitGenerator implements Generator
{
    use GeneratorHelperTrait;

    public function generate(GeneratorEngine $engine, $spec)
    {
        $this->engine = $engine;

        yield 'Protocol/MethodSerializerTrait.php' => $this->generateCode($spec->classes);
    }

    private function generateCode(array $classes)
    {
        yield '<?php';
        yield 'namespace Recoil\Amqp\Protocol;';
        yield;
        yield 'trait MethodSerializerTrait';
        yield '{';

        foreach ($classes as $class) {
            foreach ($class->methods as $method) {
                if (isset($method->direction) && $method->direction !== 'CS') {
                    continue;
                }

                $frameClass = $this->toBumpyCase($class->name, $method->name) . 'Frame';

                yield sprintf(
                    '    public function visit%s(%s\\%sFrame $frame)',
                    $frameClass,
                    $this->toBumpyCase($class->name),
                    $this->toBumpyCase($method->name)
                );

                $header = pack("nn", $class->id, $method->id);
                $headerHex = '';

                for ($index = 0; $index < strlen($header); ++$index) {
                    $headerHex .= sprintf('\x%02x', ord($header[$index]));
                }

                yield '    {';
                yield '        $payload = "' . $headerHex . '";';
                yield $this->generateMethodCode($class, $method);
                yield;
                yield '        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))';
                yield '             . $payload';
                yield '             . chr(AmqpConstants::FRAME_END);';
                yield '    }';
                yield;
            }
        }

        yield '}';
    }

    private function generateMethodCode($class, $method)
    {
        $this->bitArgs = [];
        $this->fixedArgs = [];

        foreach ($method->arguments as $argument) {
            $type = $this->engine->resolveArgumentType($argument);

            if ($type === 'bit') {
                $this->bitArgs[] = $argument;
                continue;
            } else {
                yield $this->flushBitArgs();
            }

            if ($this->engine->isFixedSize($type)) {
                $this->fixedArgs[] = $argument;
                continue;
            } else {
                yield $this->flushFixedArgs();
            }

            yield;
            yield '        // serialize "' . $argument->name . '" (' . $type . ')';

            if ($type === 'shortstr') {
                yield '        $payload .= $this->serializeShortString($frame->' . $this->toCamelCase($argument->name) . ');';
            } elseif ($type === 'longstr') {
                yield '        $payload .= $this->serializeLongString($frame->' . $this->toCamelCase($argument->name) . ');';
            } elseif ($type === 'table') {
                yield '        $payload .= $this->serializeTable($frame->' . $this->toCamelCase($argument->name) . ');';
            } else {
                throw new RuntimeException('Unknown type: ' . $type . '.');
            }
        }

        yield $this->flushBitArgs();
        yield $this->flushFixedArgs();
    }

    private function flushBitArgs()
    {
        $arguments = $this->bitArgs;
        $this->bitArgs = [];

        if (1 === count($arguments)) {
            yield;
            yield '        // serialize "' . $arguments[0]->name . '" (bit)';
            yield '        $payload .= pack("C", $frame->' . $this->toCamelCase($arguments[0]->name) . ' ? 1 : 0);';
        } elseif ($arguments) {
            yield;

            foreach ($arguments as $argument) {
                yield '        // serialize "' . $argument->name . '" (bit)';
            }

            foreach (array_chunk($arguments, 8) as $args) {
                yield '        $payload .= ord(';

                foreach ($args as $index => $argument) {
                    $expr = '';

                    if ($index === 0) {
                        yield '               (int) $frame->' . $this->toCamelCase($argument->name);;
                    } else {
                        yield '            | ((int) $frame->' . $this->toCamelCase($argument->name) . ' << ' . $index . ')' . $expr;
                    }
                }

                yield '        );';
            }

            // foreach ($arguments as $index => $argument) {
            //     $shift = $index % 8;

            //     if (0 === $index) {
            //         yield '        $payload .= ord(';
            //         yield '            0';
            //     } elseif (0 === $shift) {
            //         yield '         );';
            //         yield '        $byte = 0';
            //     }

            //     yield '              | ((int) $frame->' . $this->toCamelCase($argument->name) . ' << ' . $shift . ')';
            // }

            // yield '              ;';
        }
    }

    private function flushFixedArgs()
    {
        $arguments = $this->fixedArgs;
        $this->fixedArgs = [];

        if (1 === count($arguments)) {
            $argument = $arguments[0];
            $type     = $this->engine->resolveArgumentType($argument);
            $size     = $this->engine->sizeInBytes($type);
            $format   = $this->engine->packFormat($type);
            $name     = $this->toCamelCase($argument->name);

            yield;
            yield '        // serialize "' . $name . '" (' . $type . ')';
            yield '        $payload .= pack(' . var_export($format, true) . ', $frame->' . $name . ');';
        } elseif ($arguments) {
            $size    = 0;
            $names   = [];
            $format  = [];
            $comment = [];

            yield;

            foreach ($arguments as $argument) {
                $type = $this->engine->resolveArgumentType($argument);
                $size += $this->engine->sizeInBytes($type);
                $names[] = $this->toCamelCase($argument->name);
                $format[] = $this->engine->packFormat($type);

                yield '        // serialize "' . $argument->name . '" (' . $type . ')';
            }

            $format = implode('', $format);
            $names = '$frame->' . implode(', $frame->', $names);

            yield '        $payload .= pack(' . var_export($format, true) . ', ' . $names . ');';
        }
    }

    private $engine;
    private $bitArgs;
    private $fixedArgs;
}
