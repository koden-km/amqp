<?php

namespace Recoil\Amqp\v091\Transport;

use Eloquent\Phony\Phpunit\Phony;
use Exception;
use LogicException;
use PHPUnit_Framework_TestCase;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\Exception\ProtocolException;
use Recoil\Amqp\PackageInfo;
use Recoil\Amqp\PromiseTestTrait;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionOpenFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionOpenOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneOkFrame;
use Recoil\Amqp\v091\Protocol\HeartbeatFrame;

class HandshakeControllerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->loop = Phony::fullMock(LoopInterface::class);
        $this->timer = Phony::fullMock(TimerInterface::class);
        $this->options = ConnectionOptions::create();
        $this->timeout = 3.1415;
        $this->transport = Phony::fullMock(Transport::class);

        $this->loop->addTimer->returns($this->timer->mock());

        $this->subject = new HandshakeController(
            $this->loop->mock(),
            $this->options,
            $this->timeout
        );
    }

    /**
     * Set-up the transport mock to return some canned frames at each step of
     * the handshake.
     *
     * Use null for the default, or the string '<close-transport>' to close the
     * transport instead of sending a frame.
     *
     * @param IncomingFrame|null|string $startFrame  The frame to receive after the protocol header is sent.
     * @param IncomingFrame|null|string $tuneFrame   The frame to receive after the Start-Ok frame is sent.
     * @param IncomingFrame|null|string $openOkFrame The frame to receive after the Open frame is sent.
     */
    public function setUpTransport(
        $startFrame = null,
        $tuneFrame = null,
        $openOkFrame = null
    ) {
        $this->transport->resume->does(
            function () use ($startFrame) {
                if ('<close-transport>' === $startFrame) {
                    $this->subject->onTransportClosed();
                } else {
                    try {
                        $this->subject->onFrame(
                            $startFrame ?: ConnectionStartFrame::create(
                                0,    // channel
                                null, // versionMajor
                                null, // versionMinor
                                null, // serverProperties
                                'PLAIN AMQPLAIN BLAH', // mechanisms
                                null // locales
                            )
                        );
                    } catch (Exception $e) {
                        $this->subject->onTransportClosed($e);
                    }
                }
            }
        );

        $this->transport->send->with(
            $this->isInstanceOf(ConnectionStartOkFrame::class)
        )->does(
            function () use ($tuneFrame) {
                if ('<close-transport>' === $tuneFrame) {
                    $this->subject->onTransportClosed();
                } else {
                    try {
                        $this->subject->onFrame(
                            $tuneFrame ?: ConnectionTuneFrame::create(
                                0,   // channel
                                100, // channel max
                                200, // frame size max
                                300  // heartbeat interval
                            )
                        );
                    } catch (Exception $e) {
                        $this->subject->onTransportClosed($e);
                    }
                }
            }
        );

        $this->transport->send->with(
            $this->isInstanceOf(ConnectionOpenFrame::class)
        )->does(
            function () use ($openOkFrame) {
                if ('<close-transport>' === $openOkFrame) {
                    $this->subject->onTransportClosed();
                } else {
                    try {
                        $this->subject->onFrame(
                            $openOkFrame ?: ConnectionOpenOkFrame::create()
                        );
                    } catch (Exception $e) {
                        $this->subject->onTransportClosed($e);
                    }
                }
            }
        );
    }

    public function testStart()
    {
        $this->setUpTransport();

        $promise = $this->subject->start($this->transport->mock());

        Phony::inOrder(
            $this->loop->addTimer->calledWith(
                $this->timeout,
                [$this->subject, 'onTimeout']
            ),
            $this->transport->resume->calledWith($this->subject),
            $this->transport->send->calledWith(
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
                    "\x05LOGINS\x00\x00\x00\x05guest\x08PASSWORDS\x00\x00\x00\x05guest",
                    'en_US'
                )
            ),
            $this->transport->send->calledWith(
                ConnectionTuneOkFrame::create(
                    0,
                    100, // maximumChannelCount
                    200, // maximumFrameSize
                    300  // heartbeatInterval
                )
            ),
            $this->transport->send->calledWith(
                ConnectionOpenFrame::create(
                    0,
                    $this->options->vhost()
                )
            ),
            $this->transport->pause->called(),
            $this->timer->cancel->called()
        );

        $this->assertEquals(
            new HandshakeResult(
                100, // maximumChannelCount
                200, // maximumFrameSize
                300  // heartbeatInterval
            ),
            $this->assertResolved($promise)
        );
    }

    public function testStartWhenAlreadyStarted()
    {
        $this->setUpTransport();

        $this->subject->start($this->transport->mock());

        $this->setExpectedException(
            LogicException::class,
            'Controller has already been started.'
        );

        $this->subject->start($this->transport->mock());
    }

    public function testHandshakeWithUnlimitedChannels()
    {
        $this->setUpTransport(
            null,
            ConnectionTuneFrame::create(
                0,   // channel
                0,   // channel max (unlimited)
                200, // frame size max
                300  // heartbeat interval
            )
        );

        $this->assertEquals(
            new HandshakeResult(
                HandshakeResult::MAX_CHANNELS,
                200,   // maximumFrameSize
                300    // heartbeatInterval
            ),
            $this->assertResolved(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testHandshakeWithChannelsGreaterThanMax()
    {
        $this->setUpTransport(
            null,
            ConnectionTuneFrame::create(
                0,   // channel
                HandshakeResult::MAX_CHANNELS + 1,
                200, // frame size max
                300  // heartbeat interval
            )
        );

        $this->assertEquals(
            new HandshakeResult(
                HandshakeResult::MAX_CHANNELS,
                200, // maximumFrameSize
                300  // heartbeatInterval
            ),
            $this->assertResolved(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testHandshakeWithUnlimitedFrameSize()
    {
        $this->setUpTransport(
            null,
            ConnectionTuneFrame::create(
                0,   // channel
                100, // channel max
                0,   // frame size max (unlimited)
                300  // heartbeat interval
            )
        );

        $this->assertEquals(
            new HandshakeResult(
                100, // maximumChannelCount
                HandshakeResult::MAX_FRAME_SIZE,
                300  // heartbeatInterval
            ),
            $this->assertResolved(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testHandshakeWithFrameSizeGreaterThanMax()
    {
        $this->setUpTransport(
            null,
            ConnectionTuneFrame::create(
                0,   // channel
                100, // channel max
                HandshakeResult::MAX_FRAME_SIZE + 1,
                300  // heartbeat interval
            )
        );

        $this->assertEquals(
            new HandshakeResult(
                100, // maximumChannelCount
                HandshakeResult::MAX_FRAME_SIZE,
                300  // heartbeatInterval
            ),
            $this->assertResolved(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testHandshakeWithNoAmqPlainSupport()
    {
        $this->setUpTransport(
            ConnectionStartFrame::create(
                0,    // channel
                null, // versionMajor
                null, // versionMinor
                null, // serverProperties
                'NOTAMQPLAIN', // mechanisms
                null // locales
            )
        );

        $promise = $this->subject->start($this->transport->mock());

        $this->timer->cancel->called();

        $this->assertEquals(
            ConnectionException::handshakeFailed(
                $this->options,
                'the AMQPLAIN authentication mechanism is not supported'
            ),
            $this->assertRejected($promise)
        );
    }

    public function testHandshakeWithDisabledHeartbeat()
    {
        $this->setUpTransport(
            null,
            ConnectionTuneFrame::create(
                0,   // channel
                100, // channel max
                200, // frame size max
                0    // heartbeat interval (disabled)
            )
        );

        $this->assertEquals(
            new HandshakeResult(
                100, // maximumChannelCount
                200, // maximumFrameSize
                null // heartbeatInterval
            ),
            $this->assertResolved(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testHandshakeWithHeartbeatLessThanSpecifiedInOptions()
    {
        // @todo Use heartbeat value from connection options
        // @link https://github.com/recoilphp/amqp/issues/1
        $this->markTestIncomplete();
    }

    public function testHandshakeWithImmediatelyClosedTransport()
    {
        $this->setUpTransport(
            '<close-transport>'
        );

        $promise = $this->subject->start($this->transport->mock());

        $this->timer->cancel->called();

        $this->assertEquals(
            ConnectionException::closedUnexpectedly(
                $this->options
            ),
            $this->assertRejected($promise)
        );
    }

    public function testHandshakeWithAuthenticationFailure()
    {
        $this->setUpTransport(
            null,
            '<close-transport>'
        );

        $promise = $this->subject->start($this->transport->mock());

        $this->timer->cancel->called();

        $this->assertEquals(
            ConnectionException::authenticationFailed(
                $this->options
            ),
            $this->assertRejected($promise)
        );
    }

    public function testHandshakeWithAuthorizationFailure()
    {
        $this->setUpTransport(
            null,
            null,
            '<close-transport>'
        );

        $promise = $this->subject->start($this->transport->mock());

        $this->timer->cancel->called();

        $this->assertEquals(
            ConnectionException::authorizationFailed(
                $this->options
            ),
            $this->assertRejected($promise)
        );
    }

    public function testOnFrameWithNonZeroChannel()
    {
        $this->setExpectedException(
            ProtocolException::class,
            'The AMQP server has sent invalid data: Frame received (' . ConnectionStartFrame::class . ') on non-zero (123) channel during AMQP handshake (state: 0).'
        );

        $this->subject->onFrame(
            ConnectionStartFrame::create(
                123 // channel
            )
        );
    }

    public function testOnFrameWithUnexpectedFrameWaitStart()
    {
        $this->setUpTransport(
            HeartbeatFrame::create() // heartbeats are unexpected at this point
        );

        $promise = $this->subject->start($this->transport->mock());

        $this->assertEquals(
            ProtocolException::create(
                'Unexpected frame (' . HeartbeatFrame::class . ') received during AMQP handshake (state: 1).'
            ),
            $this->assertRejected($promise)
        );
    }

    public function testOnFrameWithUnexpectedFrameWaitTune()
    {
        $this->setUpTransport(
            null,
            HeartbeatFrame::create() // heartbeats are unexpected at this point
        );

        $promise = $this->subject->start($this->transport->mock());

        $this->assertEquals(
            ProtocolException::create(
                'Unexpected frame (' . HeartbeatFrame::class . ') received during AMQP handshake (state: 2).'
            ),
            $this->assertRejected($promise)
        );
    }

    public function testOnFrameWithUnexpectedFrameWaitOpenOk()
    {
        $this->setUpTransport(
            null,
            null,
            HeartbeatFrame::create() // heartbeats are unexpected at this point
        );

        $promise = $this->subject->start($this->transport->mock());

        $this->assertEquals(
            ProtocolException::create(
                'Unexpected frame (' . HeartbeatFrame::class . ') received during AMQP handshake (state: 3).'
            ),
            $this->assertRejected($promise)
        );
    }

    public function testOnCancel()
    {
        $promise = $this->subject->start($this->transport->mock());

        $promise->cancel();

        $this->timer->cancel->called();
        $this->transport->close->called();

        $this->assertNotSettled($promise);
    }

    public function testOnTimeout()
    {
        $promise = $this->subject->start($this->transport->mock());

        $this->subject->onTimeout();
        $this->transport->close->called();

        $this->assertEquals(
            ConnectionException::handshakeFailed(
                $this->options,
                'the handshake timed out after 3 seconds'
            ),
            $this->assertRejected($promise)
        );
    }

    use PromiseTestTrait;
}
