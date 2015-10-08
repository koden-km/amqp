<?php
use Recoil\Amqp\CodeGen\Generator;
use Recoil\Amqp\CodeGen\GeneratorEngine;
use Recoil\Amqp\CodeGen\GeneratorHelperTrait;

final class MethodReaderGenerator implements Generator
{
    use GeneratorHelperTrait;

    public function generate(GeneratorEngine $engine, $spec)
    {
        $this->engine = $engine;

        yield 'Transport/MethodReaderTrait.php' => $this->generateCode($spec->classes);
    }

    private function generateCode(array $classes)
    {
        yield '<?php';
        yield 'namespace Recoil\Amqp\Transport;';
        yield;
        yield 'trait MethodReaderTrait';
        yield '{';

        foreach ($classes as $class) {
            yield '    //';
            yield '    // AMQP class ' . $class->id . ' - ' . $class->name;
            yield '    //';
            yield;

            foreach ($class->methods as $method) {

                // Skip client->server only messages (as this is only the reader) ...
                if (isset($method->direction) && 'CS' === $method->direction) {
                    continue;
                }

                yield '    private function read' . $this->toBumpyCase($class->name, $method->name) . '()';
                yield '    {';
                yield $this->generateMethodCode($class, $method);
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

        yield '        $result = new ' . $this->toBumpyCase($class->name) . '\\' . $this->toBumpyCase($method->name) . 'Method()';

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
                yield '        $length = ord($this->buffer[0]);';
                yield '        $frame->' . $this->toCamelCase($argument->name) . ' = substr($this->buffer, 1, $length);';
                yield '        ' . $this->advanceBuffer('$length + 1');
            } elseif ($type === 'longstr') {
                yield '        list(, $length) = unpack("N", $this->buffer);';
                yield '        $frame->' . $this->toCamelCase($argument->name) . ' = substr($this->buffer, 4, $length);';
                yield '        ' . $this->advanceBuffer('$length + 4');
            } else {
                yield '        // not supported yet - ' . $type;
            }
        }

        yield $this->flushBitArgs();
        yield $this->flushFixedArgs();

        yield;
        yield '        return $result;';
    }

    private function flushBitArgs()
    {
        $arguments = $this->bitArgs;
        $this->bitArgs = [];

        if (1 === count($arguments)) {
            yield;
            yield '        // consume "' . $arguments[0]->name . '" (bit)';
            yield '        $result->' . $this->toCamelCase($arguments[0]->name) . ' = $this->buffer[0] !== "\0";';
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

return new MethodReaderGenerator;
