<?php

namespace Recoil\Amqp\Exception;

use Exception;
use RuntimeException;

/**
 * Data was received that violates the AMQP protocol specification.
 */
final class ProtocolException extends RuntimeException implements RecoilAmqpException
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
            'The AMQP server has sent invalid data: '  . rtrim($description, '.') . '.',
            0,
            $previous
        );
    }
}
