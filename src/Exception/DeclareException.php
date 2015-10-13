<?php

namespace Recoil\Amqp\Exception;

use Exception;
use Recoil\Amqp\ExchangeOptions;
use Recoil\Amqp\ExchangeType;
use Recoil\Amqp\QueueOptions;
use RuntimeException;

/**
 * An error occured while attempting to declare an exchange or queue.
 */
final class DeclareException extends RuntimeException implements RecoilAmqpException
{
    /**
     * Create an exception that indicates a failure to declare an exchange
     * because it already exists with a different type or options.
     *
     * @param string          $name     The name of the exchange.
     * @param ExchangeType    $type     The type of the exchange used in the failed attempt.
     * @param ExchangeOptions $options  The options used in the failed attempt.
     * @param Exception|null  $previous The exception that caused this exception, if any.
     *
     * @return DeclareException
     */
    public static function exchangeTypeOrOptionMismatch(
        $name,
        ExchangeType $type,
        ExchangeOptions $options,
        Exception $previous = null)
    {
        return new self(
            sprintf(
                'Failed to declare exchange "%s", type "%s" or options %s do not match the server.',
                $name,
                $type,
                $options
            ),
            0,
            $previous
        );
    }

    /**
     * Create an exception that indicates a failure to declare a queue because
     * it already exists with different options.
     *
     * @param string         $name     The name of the queue.
     * @param QueueOptions   $options  The options used in the failed attempt.
     * @param Exception|null $previous The exception that caused this exception, if any.
     *
     * @return DeclareException
     */
    public static function queueOptionMismatch(
        $name,
        QueueOptions $options,
        Exception $previous = null)
    {
        return new self(
            sprintf(
                'Failed to declare queue "%s", options %s do not match the server.',
                $name,
                $options
            ),
            0,
            $previous
        );
    }
}
