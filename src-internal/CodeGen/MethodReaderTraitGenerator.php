<?php
namespace Recoil\Amqp\CodeGen;

/**
 * @codeCoverageIgnore
 */
final class MethodReaderTraitGenerator implements Generator
{
    use GeneratorHelperTrait;

    public function generate(GeneratorEngine $engine, $spec)
    {
        $this->engine = $engine;

        yield 'Protocol/MethodReaderTrait.php' => $this->generateCode($spec->classes);
    }

    private function generateCode(array $classes)
    {
        yield '<?php';
        yield 'namespace Recoil\Amqp\Protocol;';
        yield;
        yield 'trait MethodReaderTrait';
        yield '{';
        yield '    private function readMethodFrame($channel)';
        yield '    {';
        yield '        list($class, $method) = array_values(unpack("n_1/n_2", $this->buffer));';
        yield '        ' . $this->advanceBuffer(4);
        yield;
        yield '        switch ($class) {';

        foreach ($classes as $class) {
            yield '            // class "' . $class->name . '"';
            yield '            case ' . $class->id . ':';
            yield '                switch ($method) {';

            foreach ($class->methods as $method) {
                yield '                    case ' . $method->id . ': return $this->read' . $this->toBumpyCase($class->name, $method->name) . 'Frame();';
            }

            yield '                    default:';
            yield '                        throw new RuntimeException(';
            yield '                            ' . var_export('AMQP class "' . $class->name . '" does not have a method with ID ', true) . ' . $method . \'.\'';
            yield '                        );';
            yield '                }';
            yield;
        }

        yield '            default:';
        yield '                throw new RuntimeException(';
        yield '                    ' . var_export('AMQP class "' . $class->name . '" does not have a method with ID ', true) . ' . $method . \'.\'';
        yield '                );';
        yield '        }';
        yield '    }';

        foreach ($classes as $class) {
            foreach ($class->methods as $method) {
                // Skip client->server only messages (as this is only the reader) ...
                if (isset($method->direction) && 'CS' === $method->direction) {
                    continue;
                }

                $entityClass = $this->toBumpyCase($class->name) . '\\' . $this->toBumpyCase($method->name) . 'Frame';

                yield;
                yield '    private function read' . $this->toBumpyCase($class->name, $method->name) . 'Frame()';
                yield '    {';

                if ($method->arguments) {
                    yield '        $frame = new ' . $entityClass . '();';
                    yield $this->generateMethodCode($class, $method);
                    yield;
                    yield '        return $frame;';
                } else {
                    yield '        return new ' . $entityClass . '();';
                }

                yield '    }';
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
            yield '        // consume "' . $argument->name . '" (' . $type . ')';

            if ($type === 'shortstr') {
                yield '        $frame->' . $this->toCamelCase($argument->name) . ' = $this->readShortString();';
            } elseif ($type === 'longstr') {
                yield '        $frame->' . $this->toCamelCase($argument->name) . ' = $this->readLongString();';
            } elseif ($type === 'table') {
                yield '        $frame->' . $this->toCamelCase($argument->name) . ' = $this->readTable();';
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
            yield '        // consume "' . $arguments[0]->name . '" (bit)';
            yield '        $frame->' . $this->toCamelCase($arguments[0]->name) . ' = $this->buffer[0] !== "\x00";';
            yield '        ' . $this->advanceBuffer(1);
        } elseif ($arguments) {
            yield;

            foreach ($arguments as $argument) {
                yield '        // consume "' . $argument->name . '" (bit)';
            }

            $bytes = 0;

            foreach ($arguments as $index => $argument) {
                $shift = $index % 8;

                if (0 === $shift) {
                    yield '        $octet = ord($this->buffer[' . $bytes++ . ']);';
                }

                yield '        $frame->' . $this->toCamelCase($argument->name) . ' = $octet & ' . (1 << $shift) . ' !== 0;';
            }

            yield '        ' . $this->advanceBuffer($bytes);
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
            $format   = $this->engine->unpackFormat($type);
            $name     = $this->toCamelCase($argument->name);

            yield;
            yield '        // consume "' . $name . '" (' . $type . ')';
            yield '        list(, $frame->' . $name . ') = unpack(' . var_export($format, true) . ', $this->buffer);';
            yield '        ' . $this->advanceBuffer($size);
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
                $format[] = $this->engine->unpackFormat($type, '_' . count($format));

                yield '        // consume "' . $argument->name . '" (' . $type . ')';
            }

            $format = implode('/', $format);
            $names = '$frame->' . implode(', $frame->', $names);

            yield '        list(' . $names . ') = array_values(unpack(' . var_export($format, true) . ', $this->buffer));';
            yield '        ' . $this->advanceBuffer($size);
        }
    }

    private $engine;
    private $bitArgs;
    private $fixedArgs;
}
