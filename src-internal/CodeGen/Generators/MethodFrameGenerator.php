<?php

namespace Recoil\Amqp\CodeGen\Generators;

use Recoil\Amqp\CodeGen\CodeGenerator;
use Recoil\Amqp\CodeGen\CodeGeneratorHelperTrait;

final class MethodFrameGenerator implements CodeGenerator
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
        foreach ($amqpSpec->classes as $class) {
            foreach ($class->methods as $method) {
                $filename = sprintf(
                    '%s/%s%sFrame.php',
                    $this->toBumpyCase($class->name),
                    $this->toBumpyCase($class->name),
                    $this->toBumpyCase($method->name)
                );

                yield $filename => $this->generateClass(
                    $amqpVersion,
                    $amqpSpec,
                    $class,
                    $method
                );
            }
        }
    }

    private function generateClass($amqpVersion, $amqpSpec, $class, $method)
    {
        $interfaces = [];

        yield '<?php';
        yield 'namespace Recoil\Amqp\\' . $amqpVersion . '\Protocol\\' . $this->toBumpyCase($class->name) . ';';
        yield;

        if ($this->isIncomingMethod($method)) {
            yield 'use Recoil\Amqp\\' . $amqpVersion . '\Protocol\IncomingFrame;';
            yield 'use Recoil\Amqp\\' . $amqpVersion . '\Protocol\IncomingFrameVisitor;';
            $interfaces[] = 'IncomingFrame';
        }

        if ($this->isOutgoingMethod($method)) {
            yield 'use Recoil\Amqp\\' . $amqpVersion . '\Protocol\OutgoingFrame;';
            yield 'use Recoil\Amqp\\' . $amqpVersion . '\Protocol\OutgoingFrameVisitor;';
            $interfaces[] = 'OutgoingFrame';
        }

        yield;

        $className = $this->toBumpyCase($class->name, $method->name) . 'Frame';

        yield 'final class ' . $className . ' implements ' . implode(', ', $interfaces);
        yield '{';
        yield '    public $channel;';

        foreach ($method->arguments as $argument) {
            yield '    public $' . $this->toCamelCase($argument->name) . '; // ' . $this->resolveArgumentType($amqpSpec, $argument);
        }

        yield;
        yield '    public static function create(';
        yield '        $channel = 0';

        foreach ($method->arguments as $argument) {
            yield '      , $' . $this->toCamelCase($argument->name) . ' = null';
        }

        yield '    ) {';
        yield '        $frame = new self();';
        yield;
        yield '        $frame->channel = $channel;';

        foreach ($method->arguments as $argument) {
            $name = $this->toCamelCase($argument->name);
            if (property_exists($argument, 'default-value')) {
                $default = $argument->{'default-value'};

                if (is_array($default) || is_object($default)) {
                    $default = '[]';
                } else {
                    $default = var_export($default, true);
                }

                $expression = 'null === $' . $name . ' ? ' . $default . ' : $' . $name;
            } else {
                $expression = '$' . $name;
            }
            yield '        $frame->' . $name . ' = ' . $expression . ';';
        }

        yield;
        yield '        return $frame;';
        yield '    }';
        yield;

        if ($this->isIncomingMethod($method)) {
            yield '    public function acceptIncoming(IncomingFrameVisitor $visitor)';
            yield '    {';
            yield '        return $visitor->visitIncoming' . $className . '($this);';
            yield '    }';
        }

        if ($this->isOutgoingMethod($method)) {
            yield '    public function acceptOutgoing(OutgoingFrameVisitor $visitor)';
            yield '    {';
            yield '        return $visitor->visitOutgoing' . $className . '($this);';
            yield '    }';
        }

        yield '}';
    }

    use CodeGeneratorHelperTrait;
}
