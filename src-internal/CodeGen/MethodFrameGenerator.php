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

                yield $filename => $this->generateEntityClass(
                    $engine,
                    $class,
                    $method
                );
            }
        }
    }

    private function generateEntityClass($engine, $class, $method)
    {
        yield '<?php';
        yield 'namespace Recoil\Amqp\Protocol\\' . $this->toBumpyCase($class->name) . ';';
        yield;
        yield 'use Recoil\Amqp\Protocol\Frame;';
        yield;
        yield 'final class ' . $this->toBumpyCase($method->name) . 'Frame extends Frame';
        yield '{';

        foreach ($method->arguments as $argument) {
            yield '    public $' . $this->toCamelCase($argument->name) . '; // ' . $engine->resolveArgumentType($argument);
        }

        yield '}';
    }
}
