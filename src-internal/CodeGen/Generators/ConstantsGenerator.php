<?php
namespace Recoil\Amqp\CodeGen\Generators;

use Recoil\Amqp\CodeGen\CodeGenerator;

final class ConstantsGenerator implements CodeGenerator
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
        yield 'Constants.php' => $this->generateConstantsEnumeration(
            $amqpVersion,
            $amqpSpec->constants
        );
    }

    private function generateConstantsEnumeration($amqpVersion, array $constants)
    {
        $longest = $this->sortAndFindLongest($constants);
        $format   = '    const %-' . $longest . 's = %s;';
        $previous = 0;

        yield '<?php';
        yield 'namespace Recoil\Amqp\\' . $amqpVersion . '\Protocol;';
        yield;
        yield 'final class Constants';
        yield '{';

        foreach ($constants as $constant) {
            // print an empty line if there's a gap in const values ...
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
    private function sortAndFindLongest(array &$constants)
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
