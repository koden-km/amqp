<?php
namespace Recoil\Amqp\CodeGen;

/**
 * @codeCoverageIgnore
 */
final class MethodEntityGenerator implements Generator
{
    use GeneratorHelperTrait;

    public function generate(GeneratorEngine $engine, $spec)
    {
        foreach ($spec->classes as $class) {
            foreach ($class->methods as $method) {
                $filename = sprintf(
                    'Transport/%s/%sMethod.php',
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
        yield 'namespace Recoil\Amqp\Transport\\' . $this->toBumpyCase($class->name) . ';';
        yield;
        yield 'final class ' . $this->toBumpyCase($method->name) . 'Method';
        yield '{';

        foreach ($method->arguments as $argument) {
            yield '    public $' . $this->toCamelCase($argument->name) . '; // ' . $engine->resolveArgumentType($argument);
        }

        yield '}';
    }
}
