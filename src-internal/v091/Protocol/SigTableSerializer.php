<?php

namespace Recoil\Amqp\v091\Protocol;

use Icecave\Repr\Repr;
use InvalidArgumentException;

/**
 * Serialize an AMQP table to a string buffer.
 *
 * This implementation uses the field types as discussed in the AMQP 0.9.1 SIG,
 * (*NOT* the specification) along with Qpid's extensions. This serializer is
 * suitable for use with RabbitMQ and Qpid.
 *
 * @see https://www.rabbitmq.com/amqp-0-9-1-errata.html#section_3
 *
 * @see SpecTableSerializer for an implementation based on the AMQP 0.9.1 specification.
 */
final class SigTableSerializer implements TableSerializer
{
    /**
     * Serialize an AMQP table.
     *
     * @param array $table The table.
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
            $buffer .= $this->serializeField($value);
        }

        return $this->serializeByteArray($buffer);
    }

    /**
     * Serialize a table or array field.
     *
     * @param mixed $value
     *
     * @return string The serialized value.
     */
    private function serializeField($value)
    {
        if (is_string($value)) {
            // @todo Could be decimal (D) or byte array (x)
            // @see https://github.com/recoilphp/amqp/issues/25
            return 'S' . $this->serializeLongString($value);
        } elseif (is_integer($value)) {
            // @todo Could be timestamp (T)
            // @see https://github.com/recoilphp/amqp/issues/25
            if ($value >= 0) {
                if ($value < 0x80) {
                    return 'b' . $this->serializeSignedInt8($value);
                } elseif ($value < 0x8000) {
                    return 's' . $this->serializeSignedInt16($value);
                } elseif ($value < 0x80000000) {
                    return 'I' . $this->serializeSignedInt32($value);
                }
            } else {
                if ($value >= -0x80) {
                    return 'b' . $this->serializeSignedInt8($value);
                } elseif ($value >= -0x8000) {
                    return 's' . $this->serializeSignedInt16($value);
                } elseif ($value >= -0x80000000) {
                    return 'I' . $this->serializeSignedInt32($value);
                }
            }

            return 'l' . $this->serializeSignedInt64($value);
        } elseif (true === $value) {
            return "t\x01";
        } elseif (false === $value) {
            return "t\x00";
        } elseif (null === $value) {
            return 'V';
        } elseif (is_double($value)) {
            return 'd' . $this->serializeDouble($value);
        } elseif (is_array($value)) {
            return $this->serializeArrayOrTable($value);
        } else {
            throw new InvalidArgumentException(
                'Could not serialize value (' . Repr::repr($value) . ').'
            );
        }
    }

    /**
     * Serialize a PHP array.
     *
     * If the array contains sequential integer keys, it is serialized as an AMQP
     * array, otherwise it is serialized as an AMQP table.
     *
     * @param array $array
     *
     * @return string The binary serialized table.
     */
    private function serializeArrayOrTable(array $array)
    {
        $assoc  = false;
        $index  = 0;
        $values = [];

        foreach ($array as $key => $value) {
            // We already know the array is associative, serialize both the key
            // and the value ...
            if ($assoc) {
                $values[] = $this->serializeShortString($key)
                          . $this->serializeField($value);

            // Otherwise, if the key matches the index counter it is sequential,
            // only serialize the value ...
            } elseif ($key === $index++) {
                $values[] = $this->serializeField($value);

            // Otherwise, we've just discovered the array is NOT sequential,
            // Go back through the existing values and add the keys ...
            } else {
                foreach ($values as $k => $v) {
                    $values[$k] = $this->serializeShortString($k) . $v;
                }

                $values[] = $this->serializeShortString($key)
                          . $this->serializeField($value);

                $assoc = true;
            }
        }

        return ($assoc ? 'F' : 'A') . $this->serializeByteArray(
            implode('', $values)
        );
    }

    /**
     * Serialize a byte-array.
     *
     * @param string $value The value to serialize.
     *
     * @return string The serialized value.
     */
    private function serializeByteArray($value)
    {
        return pack('N', strlen($value)) . $value;
    }

    use ScalarSerializerTrait;
}
