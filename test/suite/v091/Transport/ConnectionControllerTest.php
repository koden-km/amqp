<?php

namespace Recoil\Amqp\v091\Transport;

use Eloquent\Phony\Phpunit\Phony;
use Exception;
use LogicException;
use PHPUnit_Framework_TestCase;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Exception\ChannelException;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\PromiseTestTrait;
use Recoil\Amqp\v091\Protocol\Channel\ChannelCloseFrame;
use Recoil\Amqp\v091\Protocol\Channel\ChannelCloseOkFrame;
use Recoil\Amqp\v091\Protocol\Channel\ChannelOpenFrame;
use Recoil\Amqp\v091\Protocol\Channel\ChannelOpenOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionCloseFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionCloseOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartFrame;
use Recoil\Amqp\v091\Protocol\HeartbeatFrame;
use Recoil\Amqp\v091\Protocol\Tx\TxCommitFrame;
use Recoil\Amqp\v091\Protocol\Tx\TxCommitOkFrame;
use RuntimeException;

class ConnectionControllerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->loop = Phony::mock(LoopInterface::class);
        $this->heartbeatTimer = Phony::mock(TimerInterface::class);
        $this->closeTimeoutTimer = Phony::mock(TimerInterface::class);
        $this->options = ConnectionOptions::create();
        $this->handshakeResult = new HandshakeResult(100, 200, 300);
        $this->transport = Phony::mock(Transport::class);
        $this->transportBuilder = new MockTransportBuilder($this, $this->transport);

        $this->transportBuilder->receiveOnSend(
            ChannelOpenFrame::class,
            ChannelOpenOkFrame::create()
        );

        $this->transportBuilder->receiveOnSend(
            ChannelCloseFrame::class,
            ChannelCloseOkFrame::create()
        );

        $this->loop->addPeriodicTimer->returns($this->heartbeatTimer->mock());
        $this->loop->addTimer->returns($this->closeTimeoutTimer->mock());

        $this->subject = new ConnectionController(
            $this->loop->mock(),
            $this->options,
            $this->handshakeResult
        );
    }

    public function testStart()
    {
        $this->subject->start($this->transport->mock());

        $this->loop->addPeriodicTimer->calledWith(
            300,
            [$this->subject, 'onHeartbeat']
        );

        $this->heartbeatTimer->cancel->never()->called();

        $this->subject->onTransportClosed();

        $this->heartbeatTimer->cancel->called();
    }

    public function testStartWithoutHeartbeat()
    {
        $this->handshakeResult->heartbeatInterval = null;

        $promise = $this->subject->start($this->transport->mock());

        $this->transport->resume->calledWith($this->subject);

        $this->assertSame(
            $this->subject,
            $this->assertResolved($promise)
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

    public function testSend()
    {
        $this->subject->start($this->transport->mock());

        $frame = HeartbeatFrame::create();
        $this->subject->send($frame);

        $this->transport->send->calledWith(
            $this->identicalTo($frame)
        );
    }

    public function testSendWhenStateNotOpen()
    {
        $this->setExpectedException(
            ConnectionException::class,
            'Unable to use connection to AMQP server [localhost:5672] because it is closed.'
        );

        try {
            $this->subject->send(HeartbeatFrame::create());
        } catch (Exception $e) {
            $this->transport->noInteraction();

            throw $e;
        }
    }

    public function testWait()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->wait(HeartbeatFrame::class);

        $frame = HeartbeatFrame::create();
        $this->subject->onFrame($frame);

        $this->assertSame(
            $frame,
            $this->assertResolved($promise)
        );
    }

    public function testWaitResolvesPromisesInOrder()
    {
        $this->subject->start($this->transport->mock());

        $promise1 = $this->subject->wait(HeartbeatFrame::class);
        $promise2 = $this->subject->wait(HeartbeatFrame::class);

        $frame1 = HeartbeatFrame::create();
        $frame2 = HeartbeatFrame::create();

        $this->subject->onFrame($frame1);
        $this->subject->onFrame($frame2);

        $this->assertSame(
            $frame1,
            $this->assertResolved($promise1)
        );

        $this->assertSame(
            $frame2,
            $this->assertResolved($promise2)
        );
    }

    public function testWaitMatchesFrameType()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->wait(HeartbeatFrame::class);

        $this->subject->onFrame(
            ConnectionStartFrame::create()
        );

        $this->assertNotSettled($promise);
    }

    public function testWaitMatchesChannelId()
    {
        $this->subject->start($this->transport->mock());

        $this->subject->openChannel();

        $promise = $this->subject->wait(HeartbeatFrame::class, 1);

        $this->subject->onFrame(HeartbeatFrame::create());

        $this->assertNotSettled($promise);
    }

    public function testWaitWithClosedChannelId()
    {
        $this->subject->start($this->transport->mock());

        $this->assertEquals(
            ChannelException::notOpen(1),
            $this->assertRejected(
                $this->subject->wait(HeartbeatFrame::class, 1)
            )
        );
    }

    public function testWaitCancel()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->wait(HeartbeatFrame::class);
        $promise->cancel();

        $this->subject->onFrame(HeartbeatFrame::create());

        $this->assertNotSettled($promise);
    }

    public function testWaitPromiseIsRejectedIfTransportClosedWithException()
    {
        $this->subject->start($this->transport->mock());

        $promise1 = $this->subject->wait(HeartbeatFrame::class);
        $promise2 = $this->subject->wait(HeartbeatFrame::class);

        $exception = new Exception('The exception!');
        $this->subject->onTransportClosed($exception);

        $expected = ConnectionException::closedUnexpectedly(
            $this->options,
            $exception
        );

        $this->assertEquals(
            $expected,
            $this->assertRejected($promise1)
        );

        $this->assertEquals(
            $expected,
            $this->assertRejected($promise2)
        );
    }

    public function testWaitPromiseIsRejectedIfHeartbeatTimesOut()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->wait(HeartbeatFrame::class);

        $this->subject->onHeartbeat();

        $this->assertNotSettled($promise);

        $this->subject->onHeartbeat();

        $this->assertEquals(
            ConnectionException::heartbeatTimedOut(
                $this->options,
                300
            ),
            $this->assertRejected($promise)
        );
    }

    public function testWaitWhenStateNotOpen()
    {
        $this->assertEquals(
            ConnectionException::notOpen($this->options),
            $this->assertRejected(
                $this->subject->wait(HeartbeatFrame::class)
            )
        );
    }

    public function testListen()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->listen(HeartbeatFrame::class);
        $this->captureNotifications($promise);

        $frame = HeartbeatFrame::create();
        $this->subject->onFrame($frame);
        $this->subject->onFrame($frame);

        $this->assertSame(
            [$frame, $frame],
            $this->notifications($promise)
        );

        $this->assertNotSettled($promise);
    }

    public function testListenCancel()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->listen(HeartbeatFrame::class);
        $this->captureNotifications($promise);
        $promise->cancel();

        $this->subject->onFrame(HeartbeatFrame::create());

        $this->assertSame(
            [],
            $this->notifications($promise)
        );

        $this->assertNotSettled($promise);
    }

    public function testListenNotifiesMultiplePromises()
    {
        $this->subject->start($this->transport->mock());

        $promise1 = $this->subject->listen(HeartbeatFrame::class);
        $promise2 = $this->subject->listen(HeartbeatFrame::class);

        $this->captureNotifications($promise1);
        $this->captureNotifications($promise2);

        $frame = HeartbeatFrame::create();

        $this->subject->onFrame($frame);

        $this->assertSame(
            [$frame],
            $this->notifications($promise1)
        );

        $this->assertSame(
            [$frame],
            $this->notifications($promise2)
        );

        $this->assertNotSettled($promise1);
        $this->assertNotSettled($promise2);
    }

    public function testListenMatchesFrameType()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->listen(HeartbeatFrame::class);
        $this->captureNotifications($promise);

        $this->subject->onFrame(
            ConnectionStartFrame::create()
        );

        $this->assertSame(
            [],
            $this->notifications($promise)
        );

        $this->assertNotSettled($promise);
    }

    public function testListenMatchesChannelId()
    {
        $this->subject->start($this->transport->mock());

        $this->subject->openChannel();

        $promise = $this->subject->listen(HeartbeatFrame::class, 1);
        $this->captureNotifications($promise);

        $this->subject->onFrame(HeartbeatFrame::create());

        $this->assertSame(
            [],
            $this->notifications($promise)
        );

        $this->assertNotSettled($promise);
    }

    public function testListenWithClosedChannelId()
    {
        $this->subject->start($this->transport->mock());

        $this->assertEquals(
            ChannelException::notOpen(1),
            $this->assertRejected(
                $this->subject->listen(HeartbeatFrame::class, 1)
            )
        );
    }

    public function testListenIsIgnoredIfThereIsAMatchingWaiter()
    {
        $this->subject->start($this->transport->mock());

        $listenerPromise = $this->subject->listen(HeartbeatFrame::class);
        $this->captureNotifications($listenerPromise);

        $waiterPromise = $this->subject->wait(HeartbeatFrame::class);

        $frame = HeartbeatFrame::create();
        $this->subject->onFrame($frame);

        $this->assertSame(
            $frame,
            $this->assertResolved($waiterPromise)
        );

        $this->assertSame(
            [],
            $this->notifications($listenerPromise)
        );

        $this->assertNotSettled($listenerPromise);
    }

    public function testListenPromiseIsRejectedIfTransportClosedWithException()
    {
        $this->subject->start($this->transport->mock());

        $promise1 = $this->subject->listen(HeartbeatFrame::class);
        $promise2 = $this->subject->listen(HeartbeatFrame::class);

        $exception = new Exception('The exception!');
        $this->subject->onTransportClosed($exception);

        $expected = ConnectionException::closedUnexpectedly(
            $this->options,
            $exception
        );

        $this->assertEquals(
            $expected,
            $this->assertRejected($promise1)
        );

        $this->assertEquals(
            $expected,
            $this->assertRejected($promise2)
        );
    }

    public function testListenPromiseIsRejectedIfHeartbeatTimesOut()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->listen(HeartbeatFrame::class);

        $this->subject->onHeartbeat();

        $this->assertNotSettled($promise);

        $this->subject->onHeartbeat();

        $this->assertEquals(
            ConnectionException::heartbeatTimedOut(
                $this->options,
                300
            ),
            $this->assertRejected($promise)
        );
    }

    public function testListenWhenStateNotOpen()
    {
        $this->assertEquals(
            ConnectionException::notOpen($this->options),
            $this->assertRejected(
                $this->subject->listen(HeartbeatFrame::class)
            )
        );
    }

    public function testCapabilities()
    {
        $this->assertSame(
            $this->handshakeResult->serverCapabilities,
            $this->subject->capabilities()
        );
    }

    public function testOpenChannel()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->openChannel();

        $this->transport->send->calledWith(ChannelOpenFrame::create(1));

        $this->assertSame(
            1,
            $this->assertResolved($promise)
        );
    }

    public function testOpenChannelWithNoAvailableChannels()
    {
        $this->handshakeResult->maximumChannelCount = 1;

        $this->subject->start($this->transport->mock());

        $this->subject->openChannel();

        $this->assertEquals(
            ChannelException::noAvailableChannels(),
            $this->assertRejected(
                $this->subject->openChannel()
            )
        );
    }

    public function testOpenChannelUsesSequentialIds()
    {
        $this->subject->start($this->transport->mock());

        $this->subject->openChannel();

        $promise = $this->subject->openChannel();
        $this->transport->send->calledWith(ChannelOpenFrame::create(2));

        $this->assertSame(
            2,
            $this->assertResolved($promise)
        );
    }

    public function testOpenChannelRecyclesIds()
    {
        $this->handshakeResult->maximumChannelCount = 2;

        $this->subject->start($this->transport->mock());

        $this->subject->openChannel();
        $this->subject->openChannel();
        $this->subject->closeChannel(1);

        $this->assertSame(
            1,
            $this->assertResolved(
                $this->subject->openChannel()
            )
        );

        $this->assertEquals(
            ChannelException::noAvailableChannels(),
            $this->assertRejected(
                $this->subject->openChannel()
            )
        );
    }

    public function testOpenChannelWhenStateNotOpen()
    {
        $this->assertEquals(
            ConnectionException::notOpen($this->options),
            $this->assertRejected(
                $this->subject->openChannel()
            )
        );
    }

    public function testOpenChannelWithUnexpectedClose()
    {
        $this->transportBuilder->closeOnSend(ChannelOpenFrame::class);

        $this->subject->start($this->transport->mock());

        $promise = $this->subject->openChannel();

        $this->assertEquals(
            ConnectionException::closedUnexpectedly($this->options),
            $this->assertRejected($promise)
        );
    }

    public function testCloseChannel()
    {
        $this->subject->start($this->transport->mock());

        $this->subject->openChannel();

        $promise = $this->subject->closeChannel(1);

        $this->transport->send->calledWith(ChannelCloseFrame::create(1));

        $this->assertResolved($promise);
    }

    public function testCloseChannelWhenChannelNotOpen()
    {
        $this->subject->start($this->transport->mock());

        $this->assertEquals(
            ChannelException::notOpen(1),
            $this->assertRejected(
                $this->subject->closeChannel(1)
            )
        );
    }

    public function testCloseChannelWhenStateNotOpen()
    {
        $this->assertEquals(
            ConnectionException::notOpen($this->options),
            $this->assertRejected(
                $this->subject->closeChannel(1)
            )
        );
    }

    public function testCloseChannelWithUnexpectedClose()
    {
        $this->transportBuilder->closeOnSend(ChannelCloseFrame::class);

        $this->subject->start($this->transport->mock());

        $this->subject->openChannel();
        $promise = $this->subject->closeChannel(1);

        $this->assertEquals(
            ConnectionException::closedUnexpectedly($this->options),
            $this->assertRejected($promise)
        );
    }

    public function testServerInitializedChannelClose()
    {
        $this->subject->start($this->transport->mock());

        $this->subject->openChannel();

        $waiterPromise = $this->subject->wait(HeartbeatFrame::class, 1);
        $listenerPromise = $this->subject->listen(HeartbeatFrame::class, 1);

        $this->subject->onFrame(ChannelCloseFrame::create(1));

        $exception = ChannelException::closedUnexpectedly(
            1,
            new RuntimeException()
        );

        $this->assertEquals(
            $exception,
            $this->assertRejected($waiterPromise)
        );

        $this->assertEquals(
            $exception,
            $this->assertRejected($listenerPromise)
        );
    }

    public function testServerInitializedChannelCloseWithError()
    {
        $this->markTestIncomplete();
    }

    public function testClose()
    {
        $this->subject->start($this->transport->mock());

        $waiterPromise = $this->subject->wait(HeartbeatFrame::class);
        $listenerPromise = $this->subject->listen(HeartbeatFrame::class);

        $this->subject->close();

        $this->loop->addTimer->calledWith(
            5, // timeout
            [$this->transport->mock(), 'close']
        );

        $this->transport->send->calledWith(
            ConnectionCloseFrame::create()
        );

        $this->assertNull(
            $this->assertRejected($waiterPromise)
        );

        $this->assertNull(
            $this->assertResolved($listenerPromise)
        );

        $this->transport->close->never()->called();

        $this->subject->onFrame(ConnectionCloseOkFrame::create());

        $this->transport->close->called();

        $this->subject->onTransportClosed();

        $this->closeTimeoutTimer->cancel->called();
    }

    public function testCloseWhenStateNotOpen()
    {
        $this->subject->close();

        $this->loop->noInteraction();
        $this->transport->noInteraction();
    }

    public function testServerInitiatedClose()
    {
        $this->subject->start($this->transport->mock());

        $waiterPromise = $this->subject->wait(HeartbeatFrame::class);
        $listenerPromise = $this->subject->listen(HeartbeatFrame::class);

        $this->subject->onFrame(ConnectionCloseFrame::create());

        $exception = ConnectionException::closedUnexpectedly(
            $this->options,
            new RuntimeException()
        );

        $this->assertEquals(
            $exception,
            $this->assertRejected($waiterPromise)
        );

        $this->assertEquals(
            $exception,
            $this->assertRejected($listenerPromise)
        );

        Phony::inOrder(
            $this->transport->send->calledWith(ConnectionCloseOkFrame::create()),
            $this->transport->close->called()
        );
    }

    public function testServerInitializedCloseWithError()
    {
        $this->markTestIncomplete();
    }

    public function testHeartbeatSendsHeartbeatFrame()
    {
        $this->subject->start($this->transport->mock());

        $this->subject->onHeartbeat();

        $this->transport->send->calledWith(
            HeartbeatFrame::create()
        );
    }

    public function testHeartbeatDoesNotSendHeartbeatFrameIfFrameSent()
    {
        $this->subject->start($this->transport->mock());

        // test with a frame that is not directly meaningful to the controller
        $this->subject->send(TxCommitFrame::create());

        $this->subject->onHeartbeat();

        $this->transport->send->never()->calledWith(
            HeartbeatFrame::create()
        );

        $this->subject->onHeartbeat();

        $this->transport->send->calledWith(
            HeartbeatFrame::create()
        );
    }

    public function testHeartbeatTimesOutAfterTwoTicks()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->wait(HeartbeatFrame::class);

        $this->subject->onHeartbeat();

        $this->transport->close->never()->called();
        $this->assertNotSettled($promise);

        $this->subject->onHeartbeat();

        $this->transport->close->called();
        $this->assertEquals(
            ConnectionException::heartbeatTimedOut(
                $this->options,
                300
            ),
            $this->assertRejected($promise)
        );
    }

    public function testHeartbeatDoesNotTimeoutIfFrameReceived()
    {
        $this->subject->start($this->transport->mock());

        $this->subject->onHeartbeat();

        $this->transport->close->never()->called();
        $this->heartbeatTimer->cancel->never()->called();

        // test with a frame that is not directly meaningful to the controller
        $this->subject->onFrame(TxCommitOkFrame::create());

        $this->subject->onHeartbeat();

        $this->transport->close->never()->called();
        $this->heartbeatTimer->cancel->never()->called();
    }

    use PromiseTestTrait;
}
