<?php

namespace Recoil\Amqp\v091\Protocol;

use Recoil\Amqp\Exception\ProtocolException;

/**
 * Parse an AMQP table from a string buffer.
 *
 * This implementation uses the field types as discussed in the AMQP 0.9.1 SIG,
 * (*NOT* the specification) along with Qpid's extensions. This parser is suitable
 * for use with RabbitMQ and Qpid.
 *
 * @see https://www.rabbitmq.com/amqp-0-9-1-errata.html#section_3
 *
 * @see SpecTableParser for an implementation based on the AMQP 0.9.1 specification.
 */
final class SigTableParser implements TableParser
{
    /**
     * Retrieve the next frame from the internal buffer.
     *
     * @param string &$buffer Binary data containing the table.
     *
     * @return array             The table.
     * @throws ProtocolException if the incoming data does not conform to the
     *                           AMQP specification.
     */
    public function parse(&$buffer)
    {
        $this->buffer = &$buffer;

        return $this->parseTable();
    }

    /**
     * Parse an AMQP table from the head of the buffer.
     *
     * @return array
     */
    private function parseTable()
    {
        $length = $this->parseUnsignedInt32();
        $stopAt = strlen($this->buffer) - $length;
        $table  = [];

        while (strlen($this->buffer) > $stopAt) {
            $table[$this->parseShortString()] = $this->parseField();
        }

        return $table;
    }

    /**
     * Parse a table or array field.
     *
     * @return mixed
     */
    private function parseField()
    {
        $type = $this->buffer[0];
        $this->buffer = substr($this->buffer, 1);

        // @todo bench switch vs if vs method map
        switch ($type) {
            case 's': return $this->parseSignedInt16();
            case 'l': return $this->parseSignedInt64();
            case 'x': return $this->parseByteArray();
            case 't': return $this->parseUnsignedInt8() !== 0;
            case 'b': return $this->parseSignedInt8();
            case 'I': return $this->parseSignedInt32();
            case 'f': return $this->parseFloat();
            case 'd': return $this->parseDouble();
            case 'D': return $this->parseDecimal();
            case 'S': return $this->parseLongString();
            case 'A': return $this->parseArray();
            case 'T': return $this->parseUnsignedInt64();
            case 'F': return $this->parseTable();
            case 'V': return null;
        }

        throw ProtocolException::create(
            sprintf(
                'table value type (0x%02x) is invalid or unrecognised.',
                ord($type)
            )
        );
    }

    /**
     * Parse an AMQP decimal from the head of the buffer.
     *
     * @return float
     */
    public function parseDecimal()
    {
        $scale = $this->parseUnsignedInt8();
        $value = $this->parseSignedInt32();

        if (0 === $scale) {
            return (string) $value;
        }

        if ($value >= 0) {
            $sign = '';
            $value = (string) $value;
        } else {
            $sign = '-';
            $value = (string) -$value;
        }

        $length = strlen($value);

        if ($length === $scale) {
            return $sign . '0.' . $value;
        } elseif ($length < $scale) {
            return $sign . '0.' . str_repeat('0', $scale - $length) . $value;
        }

        return $sign . substr($value, 0, -$scale) . '.' . substr($value, -$scale);
    }

    /**
     * Parse an AMQP field-array value.
     *
     * @return array
     */
    private function parseArray()
    {
        $length = $this->parseUnsignedInt32();
        $stopAt = strlen($this->buffer) - $length;
        $array  = [];

        while (strlen($this->buffer) > $stopAt) {
            $array[] = $this->parseField();
        }

        return $array;
    }

    /**
     * Parse an AMQP byte-array value.
     *
     * @return array
     */
    private function parseByteArray()
    {
        list(, $length) = unpack('N', $this->buffer);

        try {
            return substr($this->buffer, 4, $length);
        } finally {
            $this->buffer = substr($this->buffer, $length + 4);
        }
    } // @codeCoverageIgnore

    use ScalarParserTrait;

    /**
     * @var string A buffer containing the table data.
     */
    private $buffer = '';
}
