<?php
namespace Recoil\Amqp\CodeGen;

/**
 * @codeCoverageIgnore
 */
final class FrameVisitorInterfaceGenerator implements Generator
{
    use GeneratorHelperTrait;

    public function generate(GeneratorEngine $engine, $spec)
    {
        yield 'Protocol/OutgoingFrameVisitor.php' => $this->generateCode($spec->classes, 'OutgoingFrameVisitor', 'CS');
        yield 'Protocol/IncomingFrameVisitor.php' => $this->generateCode($spec->classes, 'IncomingFrameVisitor', 'SC');
    }

    private function generateCode(array $classes, $name, $direction)
    {
        yield '<?php';
        yield 'namespace Recoil\Amqp\Protocol;';
        yield;
        yield 'interface ' . $name;
        yield '{';

        foreach ($classes as $class) {
            foreach ($class->methods as $method) {
                if (isset($method->direction) && $method->direction !== $direction) {
                    continue;
                }

                $frameClass = $this->toBumpyCase($class->name, $method->name) . 'Frame';

                yield sprintf(
                    '    public function visit%s(%s\\%sFrame $frame);',
                    $frameClass,
                    $this->toBumpyCase($class->name),
                    $this->toBumpyCase($method->name)
                );
            }
        }

        yield '}';
    }
}
