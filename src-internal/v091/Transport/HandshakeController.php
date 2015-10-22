<?php

namespace Recoil\Amqp\v091\Transport;

use Exception;
use function React\Promise\reject;
use LogicException;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
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

/**
 * A transport controller that completes the AMQP handshake.
 */
final class HandshakeController implements TransportController
{
    /**
     * @param LoopInterface     $loop    The event loop used for the handshake
     *                                   timeout timer.
     * @param ConnectionOptions $options The options used when establishing the
     *                                   connection.
     * @param integer|float     $timeout The time (in seconds) to allow for the
     *                                   handshake to complete.
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
     * Start the handshake process.
     *
     * @param Transport $transport The transport to manage.
     *
     * @return mixed          [via promise] If the controller's work is completed
     *                        successfully (implementation defined).
     * @throws Exception      [via promise] If the controller encounters an error
     *                        (implementation defined).
     * @throws LogicException If the controller has been started previously.
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

        $this->state = self::STATE_WAIT_START;

        $this->transport = $transport;
        $this->transport->resume($this);

        return $this->deferred->promise();
    }

    /**
     * Notify the controller of an incoming frame.
     *
     * @param IncomingFrame $frame The received frame.
     *
     * @throws Exception The implementation may throw any exception, which closes
     *                   the transport.
     */
    public function onFrame(IncomingFrame $frame)
    {
        if (0 !== $frame->channel) {
            throw ProtocolException::create(
                sprintf(
                    'Frame received (%s) on non-zero (%d) channel during AMQP handshake (state: %d).',
                    get_class($frame),
                    $frame->channel,
                    $this->state
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
                    'Unexpected frame (%s) received during AMQP handshake (state: %d).',
                    get_class($frame),
                    $this->state
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
        if (null === $exception) {
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
            ConnectionException::handshakeFailed(
                $this->options,
                'the handshake timed out'
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
        if ($frame->versionMajor !== 0 || $frame->versionMinor !== 9) {
            throw ConnectionException::handshakeFailed(
                $this->options,
                sprintf(
                    'the server reported an unexpected AMQP version (v%d.%d)',
                    $frame->versionMajor,
                    $frame->versionMinor
                )
            );
        }

        if (!preg_match('/\bAMQPLAIN\b/', $frame->mechanisms)) {
            throw ConnectionException::handshakeFailed(
                $this->options,
                'the AMQPLAIN authentication mechanism is not supported'
            );
        }

        // Serialize credentials in "AMQPLAIN" format, which is essentially an
        // AMQP table without the 4-byte size header ...
        $user = $this->options->username();
        $pass = $this->options->password();

        $credentials = "\x05LOGINS"    . pack('N', strlen($user)) . $user
                     . "\x08PASSWORDS" . pack('N', strlen($pass)) . $pass;

        $this->state = self::STATE_WAIT_TUNE;

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
    }

    private function onTuneFrame(ConnectionTuneFrame $frame)
    {
        if (
               $frame->channelMax !== self::UNLIMITED_CHANNELS
            && $frame->channelMax < $this->handshakeResult->maximumChannelCount
        ) {
            $this->handshakeResult->maximumChannelCount = $frame->channelMax;
        }

        if (
               $frame->frameMax !== self::UNLIMITED_FRAME_SIZE
            && $frame->frameMax < $this->handshakeResult->maximumFrameSize
        ) {
            $this->handshakeResult->maximumFrameSize = $frame->frameMax;
        }

        $optionsHeartbeat = $this->options->heartbeatInterval();
        if ($frame->heartbeat === self::HEARTBEAT_DISABLED) {
            $this->handshakeResult->heartbeatInterval = null;
        } else if (
            null !== $optionsHeartbeat
            && $optionsHeartbeat < $frame->heartbeat
        ) {
            $this->handshakeResult->heartbeatInterval = $optionsHeartbeat;
        } else {
            $this->handshakeResult->heartbeatInterval = $frame->heartbeat;
        }

        $this->state = self::STATE_WAIT_OPEN_OK;

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
    }

    private function onOpenOkFrame(ConnectionOpenOkFrame $frame)
    {
        $this->transport->pause();
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
     * The server sends channelMax of zero in the tune frame if it does not impose
     * a channel limit.
     */
    const UNLIMITED_CHANNELS = 0;

    /**
     * The server sends frameMax of zero in the tune frame if it does not impose
     * a frame size limit.
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
     * @var integer|float The time (in seconds) to allow for the handshake to
     *                    complete.
     */
    private $timeout;

    /**
     * @var HandshakeResult The result of the handshake, as produced by start().
     */
    private $handshakeResult;

    /**
     * @var integer The current state of the controller; one of the self::STATE_*
     *              constants. Represents the progress of the handshake.
     */
    private $state;

    /**
     * @var Transport The transport that this controller is managing.
     */
    private $transport;

    /**
     * @var Deferred|null The deferred object that is settled with the handshake
     *                    result upon completion.
     */
    private $deferred;

    /**
     * @var TimerInterface|null The handshake timeout timer.
     */
    private $timer;
}
