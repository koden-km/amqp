<?php
namespace Recoil\Amqp\Protocol;

use Exception;
use RuntimeException;

/**
 * Data was received that violates the constraints of the AMQP protocol.
 */
final class ProtocolException extends RuntimeException
{
    /**
     * A queue could not be found.
     *
     * @param string         $description A description of the violation.
     * @param Exception|null $previous    The exception that caused this exception, if any.
     *
     * @return ResourceNotFoundException
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
