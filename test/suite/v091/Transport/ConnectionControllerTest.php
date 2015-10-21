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
use Recoil\Amqp\PromiseTestTrait;
use Recoil\Amqp\v091\Protocol\Channel\ChannelOpenFrame;
use Recoil\Amqp\v091\Protocol\Channel\ChannelOpenOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartFrame;
use Recoil\Amqp\v091\Protocol\HeartbeatFrame;

class ConnectionControllerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->loop = Phony::fullMock(LoopInterface::class);
        $this->timer = Phony::fullMock(TimerInterface::class);
        $this->options = ConnectionOptions::create();
        $this->handshakeResult = new HandshakeResult(100, 200, 300);
        $this->transport = Phony::fullMock(Transport::class);
        $this->transportBuilder = new MockTransportBuilder($this, $this->transport);

        $this->loop->addPeriodicTimer->returns($this->timer->mock());

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

        $this->timer->cancel->never()->called();

        $this->subject->onTransportClosed();

        $this->timer->cancel->called();
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

        $promise = $this->subject->wait(HeartbeatFrame::class, 123);

        $this->subject->onFrame(
            HeartbeatFrame::create()
        );

        $this->assertNotSettled($promise);
    }

    public function testWaitCancel()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->wait(HeartbeatFrame::class);
        $promise->cancel();

        $this->subject->onFrame(
            HeartbeatFrame::create()
        );

        $this->assertNotSettled($promise);
    }

    public function testWaitPromiseIsRejectedIfTransportClosed()
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

    public function testWaitWhenNotStarted()
    {
        $this->setExpectedException(
            LogicException::class,
            'Controller is not open.'
        );

        $this->subject->wait(HeartbeatFrame::class);
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
    }

    public function testListenCancel()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->listen(HeartbeatFrame::class);
        $this->captureNotifications($promise);
        $promise->cancel();

        $this->subject->onFrame(
            HeartbeatFrame::create()
        );

        $this->assertSame(
            [],
            $this->notifications($promise)
        );
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
    }

    public function testListenMatchesChannelId()
    {
        $this->subject->start($this->transport->mock());

        $promise = $this->subject->listen(HeartbeatFrame::class, 123);
        $this->captureNotifications($promise);

        $this->subject->onFrame(
            HeartbeatFrame::create()
        );

        $this->assertSame(
            [],
            $this->notifications($promise)
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
    }

    public function testListenPromiseIsResolvedIfTransportClosed()
    {
        $this->markTestIncomplete();
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

    public function testListenWhenNotStarted()
    {
        $this->setExpectedException(
            LogicException::class,
            'Controller is not open.'
        );

        $this->subject->listen(HeartbeatFrame::class);
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

        $this->subject->send(ChannelOpenFrame::create());

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

        $this->subject->onHeartbeat();

        $this->transport->close->never()->called();
        $this->timer->cancel->never()->called();

        $this->subject->onHeartbeat();

        $this->transport->close->called();
        $this->timer->cancel->called();
    }

    public function testHeartbeatDoesNotTimeOutIfFrameReceived()
    {
        $this->subject->start($this->transport->mock());

        $this->subject->onHeartbeat();

        $this->transport->close->never()->called();
        $this->timer->cancel->never()->called();

        $this->subject->onFrame(ChannelOpenOkFrame::create());

        $this->subject->onHeartbeat();

        $this->transport->close->never()->called();
        $this->timer->cancel->never()->called();
    }

    use PromiseTestTrait;
}