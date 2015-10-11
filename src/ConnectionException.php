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
     * Create an exception that indicates that the credentials specified in the
     * connection options do not grant access to the requested vhost.
     *
     * @param ConnectionOptions $options
     * @param Exception|null    $previous The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function authorizationFailed(
        ConnectionOptions $options,
        Exception $previous = null
    ) {
        return new self(
            sprintf(
                'Unable to access vhost "%s" as "%s" on AMQP server [%s:%d], check permissions.',
                $options->vhost(),
                $options->username(),
                $options->host(),
                $options->port()
            ),
            0,
            $previous
        );
    }

    /**
     * Create an exception that indicates a connection closure due to an AMQP
     * heartbeat timeout.
     *
     * @param ConnectionOptions $options
     * @param Exception|null    $previous The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function heartbeatTimedOut(
        ConnectionOptions $options,
        $heartbeatInterval,
        Exception $previous = null
    ) {
        return new self(
            sprintf(
                'The AMQP connection with server [%s:%d] has timed out, heartbeat not received for over %d seconds.',
                $options->host(),
                $options->port(),
                $heartbeatInterval
            ),
            0,
            $previous
        );
    }

    /**
     * Create an exception that indicates an unexpected closure of the
     * connection to the AMQP server.
     *
     * @param ConnectionOptions $options
     * @param Exception|null    $previous The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function closedUnexpectedly(
        ConnectionOptions $options,
        Exception $previous = null
    ) {
        return new self(
            sprintf(
                'The AMQP connection with server [%s:%d] was closed unexpectedly.',
                $options->host(),
                $options->port()
            ),
            0,
            $previous
        );
    }
}
