<?php

namespace Recoil\Amqp\v091\Protocol;

use InvalidArgumentException;

/**
 * Serialize an AMQP table to a string buffer.
 *
 * This implementation uses the field types as discussed in the AMQP 0.9.1 SIG,
 * (*NOT* the specification) along with Qpid's extensions. This serializer is
 * suitable for use with RabbitMQ and Qpid.
 *
 * @link https://www.rabbitmq.com/amqp-0-9-1-errata.html#section_3
 *
 * @see SpecTableSerializer for an implementation based on the AMQP 0.9.1 specification.
 */
final class SigTableSerializer implements TableSerializer
{
    /**
     * Serialize an AMQP table.
     *
     * @param array The table.
     *
     * @return string                   The binary serialized table.
     * @throws InvalidArgumentException if the table contains unserializable
     *                                  values.
     */
    public function serialize(array $table)
    {
        $buffer = '';

        foreach ($table as $key => $value) {
            $buffer .= $this->serializeShortString($key);

            if (is_string($value)) {
                // if (strlen($value) <= 0xff) {
                //     $buffer .= 's' . $this->serializeShortString($value);
                // } else {
                    $buffer .= 'S' . $this->serializeLongString($value);
                // }
            }

            // if (is_bool($value)) {
            //     $buffer .= 't' . ord($value);
            // } elseif (is_float($value)) {
            //     $buffer .= 'd' .
            // }
            // } elseif (is_int($value)
        }

        return $this->serializeLongString($buffer);
    }

    use ScalarSerializerTrait;
}
