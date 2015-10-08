<?php
namespace Recoil\Amqp\CodeGen;

/**
 * @codeCoverageIgnore
 */
trait GeneratorHelperTrait
{
    /**
     * Join several strings and camel-case the output.
     *
     * eg: toCamelCase('Foo', 'bar-spam') === 'fooBarSpam'
     *
     * @param string,... $strings
     *
     * @return string
     */
    private function toCamelCase(...$strings)
    {
        return lcfirst(
            $this->toBumpyCase(...$strings)
        );
    }

    /**
     * Join several strings and bumpy-case the output.
     *
     * eg: toBumpyCase('Foo', 'bar-spam') === 'FooBarSpam'
     *
     * @param string,... $strings
     *
     * @return string
     */
    private function toBumpyCase(...$strings)
    {
        return str_replace(
            ' ',
            '',
            ucwords(
                preg_replace(
                    '/[^a-z]+/i',
                    ' ',
                    implode(' ', $strings)
                )
            )
        );
    }

    public function advanceBuffer($expr)
    {
        return '$this->buffer = substr($this->buffer, ' . $expr . ') ?: "";';
    }
}
