<?php
use Recoil\Amqp\CodeGen\Generator;
use Recoil\Amqp\CodeGen\GeneratorEngine;

final class ConstantsGenerator implements Generator
{
    public function generate(GeneratorEngine $engine, $spec)
    {
        yield 'Transport/AmqpConstants.php' => $this->generateCode($spec->constants);
    }

    private function generateCode(array $constants)
    {
        yield '<?php';
        yield 'namespace Recoil\Amqp\Transport;';
        yield;
        yield 'use Eloquent\Enumeration\AbstractEnumeration;';
        yield;
        yield 'final class AmqpConstants extends AbstractEnumeration';
        yield '{';

        $padding  = $this->sort($constants);
        $format   = '    const %-' . $padding . 's = %s;';
        $previous = 0;

        foreach ($constants as $constant) {
            if ($constant->value > $previous + 1) {
                yield;
            }
            $previous = $constant->value;

            yield sprintf(
                $format,
                str_replace('-', '_', $constant->name),
                var_export($constant->value, true)
            );
        }

        yield '}';

    }

    /**
     * Sort constants by value, and return length of longest constant name.
     *
     * @param array &$constants
     *
     * @return integer
     */
    private function sort(array &$constants)
    {
        $length = 0;

        uasort(
            $constants,
            function ($lhs, $rhs) use (&$length) {
                $length = max(
                    $length,
                    strlen($lhs->name),
                    strlen($rhs->name)
                );

                return $lhs->value - $rhs->value;
            }
        );

        return $length;
    }
}

return new ConstantsGenerator;
