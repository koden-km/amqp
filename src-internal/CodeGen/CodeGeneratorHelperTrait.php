<?php

namespace Recoil\Amqp\CodeGen;

use LogicException;

trait CodeGeneratorHelperTrait
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
                    '/[^a-z0-9]+/i',
                    ' ',
                    implode(' ', $strings)
                )
            )
        );
    }

    /**
     * Get the type of an AMQP method argument.
     *
     * @param object $argument The AMQP method argument from the spec.
     *
     * @return string The type.
     */
    private function resolveArgumentType($amqpSpec, $argument)
    {
        if (isset($argument->domain)) {
            foreach ($amqpSpec->domains as list($domain, $type)) {
                if ($domain === $argument->domain) {
                    return $type;
                }
            }

            // @codeCoverageIgnoreStart
            throw new LogicException('Undefined AMQP domain: ' . $argument->domain . '.');
            // @codeCoverageIgnoreEnd
        }

        return $argument->type;
    }

    private function getConstant($amqpSpec, $name)
    {
        foreach ($amqpSpec->constants as $constant) {
            if ($name === $constant->name) {
                return $constant->value;
            }
        }

        // @codeCoverageIgnoreStart
        throw new LogicException('Undefined AMQP constant: ' . $name . '.');
        // @codeCoverageIgnoreEnd
    }

    /**
     * Check if a type is fixed length.
     *
     * @param string The AMQP type.
     *
     * @return boolean
     */
    private function isFixedSize($type)
    {
        try {
            $this->sizeInBytes($type);
        } catch (LogicException $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the size of an AMQP type in bytes.
     *
     * @param string The AMQP type.
     *
     * @return integer
     * @throws LogicException if the type is not fixed-length.
     */
    private function sizeInBytes($type)
    {
        static $types = [
            'octet'     => 1,
            'short'     => 2,
            'long'      => 4,
            'longlong'  => 8,
            'timestamp' => 8,
        ];

        if (isset($types[$type])) {
            return $types[$type];
        }

        throw new LogicException('Not a fixed-length type: ' . $type . '.');
    }

    /**
     * Get the pack/unpack format string for an AMQP type.
     *
     * @param string The AMQP type.
     *
     * @return string
     * @throws LogicException if the type is not fixed-length.
     */
    private function packFormat($type)
    {
        static $formats = [
            'octet'     => 'c',
            'short'     => 'n',
            'long'      => 'N',
            'longlong'  => 'J',
            'timestamp' => 'J',
        ];

        if (isset($formats[$type])) {
            return $formats[$type];
        }

        // @codeCoverageIgnoreStart
        throw new LogicException('No pack/unpack format for type: ' . $type . '.');
        // @codeCoverageIgnoreEnd
    }

    /**
     * Check if an AMQP method is incoming (server -> client).
     *
     * @param object $method The AMQP method from the spec.
     *
     * @return boolean
     */
    private function isIncomingMethod($method)
    {
        return !isset($method->direction)
            || 'SC' === $method->direction;
    }

    /**
     * Check if an AMQP method is incoming (server -> client).
     *
     * @param object $method The AMQP method from the spec.
     *
     * @return boolean
     */
    private function isOutgoingMethod($method)
    {
        return !isset($method->direction)
            || 'CS' === $method->direction;
    }

    /**
     * Generate a PHP string literal that contains the given buffer.
     *
     * @return string $buffer
     *
     * @return string The quoted, hex-encoded buffer.
     */
    public function generateLiteralBuffer($buffer)
    {
        $buffer = bin2hex($buffer);
        $buffer = '\x' . chunk_split($buffer, 2, '\x');
        $buffer = substr($buffer, 0, -2); // trim trailing \x

        return '"' . $buffer . '"';
    }

    /**
     * Generate code that advances a buffer named '$this->buffer'.
     *
     * @param string $expr A PHP expression that evaluates to the number of bytes to advance.
     *
     * @return string
     */
    private function generateAdvanceBufferCode($expr)
    {
        return '$this->buffer = substr($this->buffer, ' . $expr . ');';
    }
}
