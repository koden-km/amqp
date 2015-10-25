<?php

namespace Recoil\Amqp\v091\Transport;

use Eloquent\Phony\Phpunit\Phony;
use LogicException;
use PHPUnit_Framework_TestCase;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\Exception\ProtocolException;
use Recoil\Amqp\PackageInfo;
use Recoil\Amqp\PromiseTestTrait;
use Recoil\Amqp\ServerCapabilities;
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
        $this->loop = Phony::mock(LoopInterface::class);
        $this->timer = Phony::mock(TimerInterface::class);
        $this->options = ConnectionOptions::create();
        $this->timeout = 3.1415;
        $this->transport = Phony::mock(Transport::class);
        $this->transportBuilder = new MockTransportBuilder($this, $this->transport);

        // Configure the transport to perform a successful handshake by default ...
        $this->transportBuilder->receiveOnResume(
            ConnectionStartFrame::create(
                0,    // channel
                null, // versionMajor
                null, // versionMinor
                null, // serverProperties
                'PLAIN AMQPLAIN BLAH', // mechanisms
                null // locales
            )
        );
        $this->transportBuilder->receiveOnSend(
            ConnectionStartOkFrame::class,
            ConnectionTuneFrame::create(
                0,
                100, // maximumChannelCount
                200, // maximumFrameSize
                300  // heartbeatInterval
            )
        );
        $this->transportBuilder->receiveOnSend(
            ConnectionOpenFrame::class,
            ConnectionOpenOkFrame::create()
        );

        $this->loop->addTimer->returns($this->timer->mock());

        $this->subject = new HandshakeController(
            $this->loop->mock(),
            $this->options,
            $this->timeout
        );
    }

    public function testHandshake()
    {
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

    /**
     * @dataProvider serverCapabilityTestVectors
     */
    public function testHandshakeServerCapabilities($frameKey, $optionKey)
    {
        $this->transportBuilder->receiveOnResume(
            ConnectionStartFrame::create(
                0,    // channel
                null, // versionMajor
                null, // versionMinor
                [
                    'capabilities' => [
                        $frameKey => true,
                    ],
                ],
                'AMQPLAIN', // mechanisms
                null // locales
            )
        );

        $expected = ServerCapabilities::none()->{$optionKey}(true);

        $this->assertEquals(
            new HandshakeResult(
                100, // maximumChannelCount
                200, // maximumFrameSize
                300,  // heartbeatInterval
                $expected
            ),
            $this->assertResolved(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function serverCapabilityTestVectors()
    {
        yield ['per_consumer_qos',           'perConsumerQos'];
        yield ['exchange_exchange_bindings', 'exchangeToExchangeBindings'];
    }

    public function testHandshakeWithUnlimitedChannels()
    {
        $this->transportBuilder->receiveOnSend(
            ConnectionStartOkFrame::class,
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
        $this->transportBuilder->receiveOnSend(
            ConnectionStartOkFrame::class,
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
        $this->transportBuilder->receiveOnSend(
            ConnectionStartOkFrame::class,
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
        $this->transportBuilder->receiveOnSend(
            ConnectionStartOkFrame::class,
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

    public function testHandshakeWithDisabledHeartbeat()
    {
        $this->transportBuilder->receiveOnSend(
            ConnectionStartOkFrame::class,
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
        $this->subject = new HandshakeController(
            $this->loop->mock(),
            $this->options->setHeartbeatInterval(30),
            $this->timeout
        );

        $this->transportBuilder->receiveOnSend(
            ConnectionStartOkFrame::class,
            ConnectionTuneFrame::create(
                0,   // channel
                100, // channel max
                200, // frame size max
                10 // heartbeat interval
            )
        );

        $this->assertEquals(
            new HandshakeResult(
                100, // maximumChannelCount
                200, // maximumFrameSize
                10 // heartbeatInterval
            ),
            $this->assertResolved(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testHandshakeWithHeartbeatSpecifiedInOptions()
    {
        $this->subject = new HandshakeController(
            $this->loop->mock(),
            $this->options->setHeartbeatInterval(5),
            $this->timeout
        );

        $this->transportBuilder->receiveOnSend(
            ConnectionStartOkFrame::class,
            ConnectionTuneFrame::create(
                0,   // channel
                100, // channel max
                200, // frame size max
                10 // heartbeat interval
            )
        );

        $this->assertEquals(
            new HandshakeResult(
                100, // maximumChannelCount
                200, // maximumFrameSize
                5 // heartbeatInterval
            ),
            $this->assertResolved(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testCanNotBeStartedTwice()
    {
        $this->subject->start($this->transport->mock());

        $this->setExpectedException(
            LogicException::class,
            'Controller has already been started.'
        );

        $this->subject->start($this->transport->mock());
    }

    public function testTimerIsCancelledUponFailure()
    {
        $this->transportBuilder->closeOnResume();

        $promise = $this->subject->start($this->transport->mock());

        $this->timer->cancel->called();

        $this->assertEquals(
            ConnectionException::closedUnexpectedly(
                $this->options
            ),
            $this->assertRejected($promise)
        );
    }

    public function testServerWithUnsupportedMajorVersion()
    {
        $this->transportBuilder->receiveOnResume(
            ConnectionStartFrame::create(
                0, // channel
                1, // versionMajor (!== 0)
                0  // versionMinor
            )
        );

        $this->assertEquals(
            ConnectionException::handshakeFailed(
                $this->options,
                'the server reported an unexpected AMQP version (v1.0)'
            ),
            $this->assertRejected(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testServerWithUnsupportedMinorVersion()
    {
        $this->transportBuilder->receiveOnResume(
            ConnectionStartFrame::create(
                0, // channel
                0, // versionMajor
                1  // versionMinor (!== 9)
            )
        );

        $this->assertEquals(
            ConnectionException::handshakeFailed(
                $this->options,
                'the server reported an unexpected AMQP version (v0.1)'
            ),
            $this->assertRejected(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testServerWithNoAmqPlainSupport()
    {
        $this->transportBuilder->receiveOnResume(
            ConnectionStartFrame::create(
                0,    // channel
                null, // versionMajor
                null, // versionMinor
                null, // serverProperties
                'NOTAMQPLAIN', // mechanisms
                null // locales
            )
        );

        $this->assertEquals(
            ConnectionException::handshakeFailed(
                $this->options,
                'the AMQPLAIN authentication mechanism is not supported'
            ),
            $this->assertRejected(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testAuthenticationFailure()
    {
        $this->transportBuilder->closeOnSend(
            ConnectionStartOkFrame::class
        );

        $this->assertEquals(
            ConnectionException::authenticationFailed(
                $this->options
            ),
            $this->assertRejected(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testAuthorizationFailure()
    {
        $this->transportBuilder->closeOnSend(
            ConnectionOpenFrame::class
        );

        $this->assertEquals(
            ConnectionException::authorizationFailed(
                $this->options
            ),
            $this->assertRejected(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testFrameWithNonZeroChannel()
    {
        $this->transportBuilder->receiveOnResume(
            ConnectionStartFrame::create(123)
        );

        $this->assertEquals(
            ProtocolException::create(
                'Frame received (' . ConnectionStartFrame::class . ') on non-zero (123) channel during AMQP handshake (state: 1).'
            ),
            $this->assertRejected(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testUnexpectedFrameInWaitStartState()
    {
        $this->transportBuilder->receiveOnResume(
            HeartbeatFrame::create() // heartbeats always unexpected DURING handshake
        );

        $this->assertEquals(
            ProtocolException::create(
                'Unexpected frame (' . HeartbeatFrame::class . ') received during AMQP handshake (state: 1).'
            ),
            $this->assertRejected(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testOnFrameWithUnexpectedFrameWaitTune()
    {
        $this->transportBuilder->receiveOnSend(
            ConnectionStartOkFrame::class,
            HeartbeatFrame::create() // heartbeats always unexpected DURING handshake
        );

        $this->assertEquals(
            ProtocolException::create(
                'Unexpected frame (' . HeartbeatFrame::class . ') received during AMQP handshake (state: 2).'
            ),
            $this->assertRejected(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testOnFrameWithUnexpectedFrameWaitOpenOk()
    {
        $this->transportBuilder->receiveOnSend(
            ConnectionOpenFrame::class,
            HeartbeatFrame::create() // heartbeats always unexpected DURING handshake
        );

        $this->assertEquals(
            ProtocolException::create(
                'Unexpected frame (' . HeartbeatFrame::class . ') received during AMQP handshake (state: 3).'
            ),
            $this->assertRejected(
                $this->subject->start($this->transport->mock())
            )
        );
    }

    public function testTimeout()
    {
        $this->transportBuilder->doNothingOnResume();

        $promise = $this->subject->start($this->transport->mock());

        $this->subject->onTimeout();
        $this->transport->close->called();

        $this->assertEquals(
            ConnectionException::handshakeFailed(
                $this->options,
                'the handshake timed out'
            ),
            $this->assertRejected($promise)
        );
    }

    public function testCancel()
    {
        $this->transportBuilder->doNothingOnResume();

        $promise = $this->subject->start($this->transport->mock());
        $promise->cancel();

        $this->timer->cancel->called();
        $this->transport->close->called();

        $this->assertNotSettled($promise);
    }

    public function testCancelDoesNothingIfComplete()
    {
        $promise = $this->subject->start($this->transport->mock());
        $promise->cancel();

        $this->transport->close->never()->called();
    }

    use PromiseTestTrait;
}
