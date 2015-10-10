<?php
namespace Recoil\Amqp;

use Exception;
use RuntimeException;

/**
 * Data was received that violates the requirements of the AMQP protocol.
 */
final class ProtocolException extends RuntimeException
{
    /**
     * Data was received that violates the requirements of the AMQP protocol.
     *
     * @param string         $description A description of the violation.
     * @param Exception|null $previous    The exception that caused this exception, if any.
     *
     * @return ProtocolException
     */
    public static function create($description, Exception $previous = null)
    {
        return new self(
            'Protocol error: '  . rtrim($description, '.') . '.',
            0,
            $previous
        );
    }
}
