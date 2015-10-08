<?php
namespace Recoil\Amqp\CodeGen;

/**
 * @codeCoverageIgnore
 */
final class MethodFrameGenerator implements Generator
{
    use GeneratorHelperTrait;

    public function generate(GeneratorEngine $engine, $spec)
    {
        foreach ($spec->classes as $class) {
            foreach ($class->methods as $method) {
                $filename = sprintf(
                    'Protocol/%s/%sFrame.php',
                    $this->toBumpyCase($class->name),
                    $this->toBumpyCase($method->name)
                );

                yield $filename => $this->generateCode(
                    $engine,
                    $class,
                    $method
                );
            }
        }
    }

    private function generateCode($engine, $class, $method)
    {
        $isIncoming = true;
        $isOutgoing = true;

        if (isset($method->direction)) {
            if ($method->direction === 'SC') {
                $isOutgoing = false;
            } elseif ($method->direction === 'CS') {
                $isIncoming = false;
            }
        }

        $interfaces = [];

        yield '<?php';
        yield 'namespace Recoil\Amqp\Protocol\\' . $this->toBumpyCase($class->name) . ';';
        yield;

        if ($isIncoming) {
            yield 'use Recoil\Amqp\Protocol\IncomingFrame;';
            yield 'use Recoil\Amqp\Protocol\IncomingFrameVisitor;';
            $interfaces[] = 'IncomingFrame';
        }

        if ($isOutgoing) {
            yield 'use Recoil\Amqp\Protocol\OutgoingFrame;';
            yield 'use Recoil\Amqp\Protocol\OutgoingFrameVisitor;';
            $interfaces[] = 'OutgoingFrame';
        }

        yield;

        $className = $this->toBumpyCase($method->name) . 'Frame';

        yield 'final class ' . $className . ' implements ' . implode(', ', $interfaces);
        yield '{';
        yield '    public $channel;';

        foreach ($method->arguments as $argument) {
            yield '    public $' . $this->toCamelCase($argument->name) . '; // ' . $engine->resolveArgumentType($argument);
        }

        if ($isIncoming) {
            yield;
            yield '    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)';
            yield '    {';
            yield '        return $visitor->visit' . $this->toBumpyCase($class->name) . $className . '($this);';
            yield '    }';
        }

        if ($isOutgoing) {
            yield;
            yield '    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)';
            yield '    {';
            yield '        return $visitor->visit' . $this->toBumpyCase($class->name) . $className . '($this);';
            yield '    }';
        }

        yield '}';
    }
}
