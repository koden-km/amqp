<?php

namespace Recoil\Amqp\v091\Transport;

use Exception;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\Exception\ProtocolException;
use Recoil\Amqp\PackageInfo;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionOpenFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionOpenOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneOkFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrame;
use RuntimeException;
use function React\Promise\reject;

/**
 * A transport controller that completes the AMQP handshake.
 */
final class HandshakeController implements TransportController
{
    /**
     * @param LoopInterface        $loop    The event loop used for the handshake timeout timer.
     * @param ConnectionOptions    $options The options used when establishing the connection.
     * @param integer|float        $timeout The time (in seconds) to allow for the handshake to complete.
     */
    public function __construct(
        LoopInterface $loop,
        ConnectionOptions $options,
        $timeout
    ) {
        $this->loop = $loop;
        $this->options = $options;
        $this->timeout = $timeout;
        $this->handshakeResult = new HandshakeResult();
        $this->state = self::STATE_STARTABLE;
    }

    /**
     * Begin managing a transport.
     *
     * @param Transport $transport The transport to manage.
     *
     * Via promise:
     * @return HandshakeResult The result of a successful AMQP handshake.
     * @throws ConnnectionException If the handshake failed for any reason.
     * @throws ProtocolException If the AMQP protocol was violated by the server.
     */
    public function start(Transport $transport)
    {
        if (self::STATE_STARTABLE !== $this->state) {
            throw new LogicException('Controller has already been started.');
        }

        $this->timer = $this->loop->addTimer(
            $this->timeout,
            [$this, 'onTimeout']
        );

        $this->deferred = new Deferred(
            [$this, 'onCancel']
        );

        $this->transport = $transport;
        $this->transport->resume($this);

        $this->state = self::STATE_WAIT_START;

        return $this->deferred->promise();
    }

    /**
     * Notify the controller of an incoming frame.
     *
     * @param IncomingFrame $frame
     */
    public function onFrame(IncomingFrame $frame)
    {
        if (0 !== $frame->channel) {
            throw ProtocolException::create(
                sprintf(
                    'Frame received (%s) on non-zero (%d) channel during AMQP handshake.',
                    get_class($frame),
                    $frame->channel
                )
            );
        } elseif (self::STATE_WAIT_START === $this->state && $frame instanceof ConnectionStartFrame) {
            $this->onStartFrame($frame);
        } elseif (self::STATE_WAIT_TUNE === $this->state && $frame instanceof ConnectionTuneFrame) {
            $this->onTuneFrame($frame);
        } elseif (self::STATE_WAIT_OPEN_OK === $this->state && $frame instanceof ConnectionOpenOkFrame) {
            $this->onOpenOkFrame($frame);
        } else {
            throw ProtocolException::create(
                sprintf(
                    'Unexpected frame (%s) received during AMQP handshake.',
                    get_class($frame)
                )
            );
        }
    }

    /**
     * Notify the controller that the transport has been closed.
     *
     * @param Exception|null $exception The error that caused the closure, if any.
     */
    public function onTransportClosed(Exception $exception = null)
    {
        if (self::STATE_DONE === $this->state) {
            return;
        } elseif (null === $exception) {
            if (self::STATE_WAIT_TUNE === $this->state) {
                $exception = ConnectionException::authenticationFailed($this->options);
            } elseif (self::STATE_WAIT_OPEN_OK === $this->state) {
                $exception = ConnectionException::authorizationFailed($this->options);
            } else {
                $exception = ConnectionException::closedUnexpectedly($this->options);
            }
        }

        $this->done();
        $this->deferred->reject($exception);
    }

    /**
     * @access private
     */
    public function onTimeout()
    {
        $this->done();
        $this->transport->close();
        $this->deferred->reject(
            ConnectionException::coultNotConnect(
                $this->options,
                new RuntimeException(
                    'AMQP handshake timed out after ' . $this->options->timeout() . ' seconds.'
                )
            )
        );
    }

    /**
     * @access private
     */
    public function onCancel()
    {
        if (self::STATE_DONE !== $this->state) {
            $this->done();
            $this->transport->close();
        }
    }

    private function onStartFrame(ConnectionStartFrame $frame)
    {
        if (!preg_match('/\bAMQPLAIN\b/', $frame->mechanisms)) {
            throw ConnectionException::handshakeFailed(
                $this->options,
                'The AMQP server does not support the AMQPLAIN authentication mechanism.'
            );
        }

        // Serialize credentials in "AMQPLAIN" format, which is essentially an
        // AMQP table without the 4-byte size header ...
        $user = $this->options->username();
        $pass = $this->options->password();

        $credentials = "\x05LOGINS"    . pack('N', strlen($user)) . $user
                     . "\x08PASSWORDS" . pack('N', strlen($pass)) . $pass
                     ;

        $this->transport->send(
            ConnectionStartOkFrame::create(
                0,
                [
                    'product'     => $this->options->productName(),
                    'version'     => $this->options->productVersion(),
                    'platform'    => PackageInfo::AMQP_PLATFORM,
                    'copyright'   => PackageInfo::AMQP_COPYRIGHT,
                    'information' => PackageInfo::AMQP_INFORMATION,
                ],
                'AMQPLAIN',
                $credentials
            )
        );

        $this->state = self::STATE_WAIT_TUNE;
    }

    private function onTuneFrame(ConnectionTuneFrame $frame)
    {
        if ($frame->channelMax === self::UNLIMITED_CHANNELS) {
            $this->handshakeResult->maximumChannelCount = self::MAX_CHANNELS;
        } elseif ($frame->channelMax > self::MAX_CHANNELS) {
            $this->handshakeResult->maximumChannelCount = self::MAX_CHANNELS;
        } else {
            $this->handshakeResult->maximumChannelCount = $frame->channelMax;
        }

        if ($frame->frameMax === self::UNLIMITED_FRAME_SIZE) {
            $this->handshakeResult->maximumFrameSize = self::MAX_FRAME_SIZE;
        } elseif ($frame->frameMax > self::MAX_FRAME_SIZE) {
            $this->handshakeResult->maximumFrameSize = self::MAX_FRAME_SIZE;
        } else {
            $this->handshakeResult->maximumFrameSize = $frame->frameMax;
        }

        // @todo Use heartbeat value from connection options
        // @link https://github.com/recoilphp/amqp/issues/1

        if ($frame->heartbeat === self::HEARTBEAT_DISABLED) {
            $this->handshakeResult->heartbeatInterval = null;
        } else {
            $this->handshakeResult->heartbeatInterval = $frame->heartbeat;
        }

        $this->transport->send(
            ConnectionTuneOkFrame::create(
                0,
                $this->handshakeResult->maximumChannelCount,
                $this->handshakeResult->maximumFrameSize,
                $this->handshakeResult->heartbeatInterval ?: 0
            )
        );

        $this->transport->send(
            ConnectionOpenFrame::create(
                0,
                $this->options->vhost()
            )
        );

        $this->state = self::STATE_WAIT_OPEN_OK;
    }

    private function onOpenOkFrame(ConnectionOpenOkFrame $frame)
    {
        $this->done();
        $this->deferred->resolve($this->handshakeResult);
    }

    private function done()
    {
        if ($this->timer) {
            $this->timer->cancel();
            $this->timer = null;
        }

        $this->state = self::STATE_DONE;
    }

    /**
     * The maximum number of channels.
     *
     * AMQP channel ID is 2 bytes, but zero is reserved for connection-level
     * communication.
     */
    const MAX_CHANNELS = 0xffff - 1;

    /**
     * The sends channelMax of zero in the tune frame if it does not impose a
     * channel limit.
     */
    const UNLIMITED_CHANNELS = 0;

    /**
     * The maximum frame size the client supports.
     *
     * Note: RabbitMQ's default is 0x20000 (128 KB), our limit is higher to
     * allow for some server-side configurability.
     */
    const MAX_FRAME_SIZE = 0x80000; // 512 KB

    /**
     * The server frameMax of zero in the tune frame if it does not impose a
     * frame size limit.
     */
    const UNLIMITED_FRAME_SIZE = 0;

    /**
     * The servers a heartbeat of zero in the tune frame if it does not use
     * heartbeats.
     */
    const HEARTBEAT_DISABLED = 0;

    /**
     * The handshake is ready to be started.
     */
    const STATE_STARTABLE = 0;

    /**
     * Waiting for a Connection.Start frame from the server.
     */
    const STATE_WAIT_START = 1;

    /**
     * Waiting for a Connection.Tune frame from the server.
     */
    const STATE_WAIT_TUNE = 2;

    /**
     * Waiting for a Connection.Open-Ok frame from the server.
     */
    const STATE_WAIT_OPEN_OK = 3;

    /**
     * The handshake has ended (either successfully or with an error).
     */
    const STATE_DONE = 4;

    /**
     * @var LoopInterface The event loop used for the handshake timeout timer.
     */
    private $loop;

    /**
     * @var ConnectionOptions The options used when establishing the connection.
     */
    private $options;

    /**
     * @var integer|float The time (in seconds) to allow for the handshake to complete.
     */
    private $timeout;

    /**
     * @var HandshakeResult|null The result of the handshake.
     */
    private $handshakeResult;

    /**
     * @var integer The current state of the handshake (one of the self::STATE_* constants).
     */
    private $state;

    /**
     * @var Transport The transport that this controller is managing.
     */
    private $transport;

    /**
     * @var Deferred|null The deferred object that is settled with the result of the handshake.
     */
    private $deferred;

    /**
     * @var TimerInterface|null The handshake timeout timer.
     */
    private $timer;
}
