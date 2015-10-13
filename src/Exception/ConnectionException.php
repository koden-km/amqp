<?php
namespace Recoil\Amqp\Exception;

use Exception;
use Recoil\Amqp\ConnectionOptions;
use RuntimeException;

/**
 * An exception used to indicate problems establishing or maintaining a
 * connection to an AMQP server.
 */
final class ConnectionException extends RuntimeException implements RecoilAmqpException
{
    /**
     * Create an exception that indicates a failure to establish a connection to
     * an AMQP server.
     *
     * @param ConnectionOptions $options  The options used when establishing the connection.
     * @param Exception|null    $previous The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function couldNotConnect(
        ConnectionOptions $options,
        Exception $previous = null
    ) {
        return new self(
            $options,
            sprintf(
                'Unable to connect to AMQP server [%s:%d], check connection options and network connectivity.',
                $options->host(),
                $options->port()
            ),
            $previous
        );
    }

    /**
     * Create an exception that indicates that the credentials specified in the
     * connection options are incorrect.
     *
     * @param ConnectionOptions $options  The options used when establishing the connection.
     * @param Exception|null    $previous The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function authenticationFailed(
        ConnectionOptions $options,
        Exception $previous = null
    ) {
        return new self(
            $options,
            sprintf(
                'Unable to authenticate as "%s" on AMQP server [%s:%d], check authentication credentials.',
                $options->username(),
                $options->host(),
                $options->port()
            ),
            $previous
        );
    }

    /**
     * Create an exception that indicates that the credentials specified in the
     * connection options do not grant access to the requested AMQP virtual host.
     *
     * @param ConnectionOptions $options  The options used when establishing the connection.
     * @param Exception|null    $previous The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function authorizationFailed(
        ConnectionOptions $options,
        Exception $previous = null
    ) {
        return new self(
            $options,
            sprintf(
                'Unable to access vhost "%s" as "%s" on AMQP server [%s:%d], check permissions.',
                $options->vhost(),
                $options->username(),
                $options->host(),
                $options->port()
            ),
            $previous
        );
    }

    /**
     * Create an exception that indicates that the server has failed to send
     * any data for a period longer than the heartbeat interval.
     *
     * @param ConnectionOptions $options           The options used when establishing the connection.
     * @param integer           $heartbeatInterval The heartbeat interval negotiated during the AMQP handshake.
     * @param Exception|null    $previous          The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function heartbeatTimedOut(
        ConnectionOptions $options,
        $heartbeatInterval,
        Exception $previous = null
    ) {
        return new self(
            $options,
            sprintf(
                'The AMQP connection with server [%s:%d] has timed out, no heartbeat received for over %d seconds.',
                $options->host(),
                $options->port(),
                $heartbeatInterval
            ),
            $previous
        );
    }

    /**
     * Create an exception that indicates an unexpected closure of the
     * connection to the AMQP server.
     *
     * @param ConnectionOptions $options  The options used when establishing the connection.
     * @param Exception|null    $previous The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function closedUnexpectedly(
        ConnectionOptions $options,
        Exception $previous = null
    ) {
        return new self(
            $options,
            sprintf(
                'The AMQP connection with server [%s:%d] was closed unexpectedly.',
                $options->host(),
                $options->port()
            ),
            $previous
        );
    }

    /**
     * Get the connection options.
     *
     * @return ConnectionOptions The options used when establishing the connection.
     */
    public function connectionOptions()
    {
        return $this->options;
    }

    /**
     * Please note that this code is not part of the public API. It may be
     * changed or removed at any time without notice.
     *
     * @access private
     *
     * This constructor is public because the `Exception` class does not allow
     * subclasses to have private or protected constructors.
     *
     * @param ConnectionOptions $options  The options used when establishing the connection.
     * @param string            $message  The exception message.
     * @param Exception|null    $previous The exception that caused this exception, if any.
     */
    public function __construct(
        ConnectionOptions $options,
        $message,
        Exception $previous = null
    ) {
        $this->options = $options;

        parent::__construct($message, 0, $previous);
    }

    private $options;
}
