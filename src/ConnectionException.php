<?php
namespace Recoil\Amqp;

use Exception;
use RuntimeException;

/**
 * An error occured while attempting to establish an AMQP connection.
 */
final class ConnectionException extends RuntimeException
{
    /**
     * Create an exception that indicates a failure to establish a connection to
     * an AMQP server.
     *
     * @param ConnectionOptions $options
     * @param Exception|null    $previous The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function couldNotConnect(
        ConnectionOptions $options,
        Exception $previous = null
    ) {
        return new self(
            sprintf(
                'Unable to connect to AMQP server [%s:%d], check connection options and network connectivity.',
                $options->host(),
                $options->port()
            ),
            0,
            $previous
        );
    }

    /**
     * Create an exception that indicates that the credentials specified in the
     * connection options are incorrect.
     *
     * @param ConnectionOptions $options
     * @param Exception|null    $previous The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function authenticationFailed(
        ConnectionOptions $options,
        Exception $previous = null
    ) {
        return new self(
            sprintf(
                'Unable to authenticate as "%s" on AMQP server [%s:%d], check authentication credentials.',
                $options->username(),
                $options->host(),
                $options->port()
            ),
            0,
            $previous
        );
    }

    /**
     * Create an exception that indicates a failure to complete the AMQP
     * handshake with the server.
     *
     * @param ConnectionOptions $options
     * @param Exception|null    $previous The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function handshakeFailed(
        ConnectionOptions $options,
        Exception $previous = null
    ) {
        return new self(
            sprintf(
                'Unable to negotiate AMQP connection with server [%s:%d], check vhost name (%s) and permissions.',
                $options->host(),
                $options->port(),
                $options->vhost()
            ),
            0,
            $previous
        );
    }

    /**
     * Create an exception that indicates the disconnection of a connection that
     * is expected to already be established.
     *
     * @param Exception|null $previous The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function notConnected(Exception $previous = null)
    {
        return new self('Disconnected from AMQP server.', 0, $previous);
    }
}
