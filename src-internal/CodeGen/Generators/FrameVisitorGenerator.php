<?php

namespace Recoil\Amqp\CodeGen\Generators;

use Recoil\Amqp\CodeGen\CodeGenerator;
use Recoil\Amqp\CodeGen\CodeGeneratorHelperTrait;

final class FrameVisitorGenerator implements CodeGenerator
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
        yield 'OutgoingFrameVisitor.php' => $this->generateVisitorInterface(
            $amqpVersion,
            $amqpSpec->classes,
            'Outgoing'
        );

        yield 'IncomingFrameVisitor.php' => $this->generateVisitorInterface(
            $amqpVersion,
            $amqpSpec->classes,
            'Incoming'
        );
    }

    private function generateVisitorInterface(
        $amqpVersion,
        array $classes,
        $direction
    ) {
        yield '<?php';
        yield 'namespace Recoil\Amqp\\' . $amqpVersion . '\Protocol;';
        yield;
        yield 'interface ' . $direction . 'FrameVisitor';
        yield '{';

        yield sprintf(
            '    public function visit%sHeartbeatFrame(HeartbeatFrame $frame);',
            $direction
        );

        foreach ($classes as $class) {
            foreach ($class->methods as $method) {
                if ($this->{'is' . $direction . 'Method'}($method)) {
                    $bumpyClass = $this->toBumpyCase($class->name);
                    $bumpyMethod = $this->toBumpyCase($method->name);

                    yield sprintf(
                        '    public function visit%s%s%sFrame(%s\\%s%sFrame $frame);',
                        $direction,
                        $bumpyClass,
                        $bumpyMethod,
                        $bumpyClass,
                        $bumpyClass,
                        $bumpyMethod
                    );
                }
            }
        }

        yield '}';
    }

    use CodeGeneratorHelperTrait;
}
