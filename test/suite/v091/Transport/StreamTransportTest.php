<?php

namespace Recoil\Amqp\v091\Transport;

use Eloquent\Phony\Phpunit\Phony;
use Exception;
use PHPUnit_Framework_TestCase;
use React\Stream\DuplexStreamInterface;
use Recoil\Amqp\v091\Protocol\FrameParser;
use Recoil\Amqp\v091\Protocol\FrameSerializer;
use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

class StreamTransportTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->stream = Phony::mock(DuplexStreamInterface::class);
        $this->parser = Phony::mock(FrameParser::class);
        $this->serializer = Phony::mock(FrameSerializer::class);
        $this->controller = Phony::mock(TransportController::class);
        $this->incomingFrame1 = Phony::mock(IncomingFrame::class);
        $this->incomingFrame2 = Phony::mock(IncomingFrame::class);
        $this->outgoingFrame = Phony::mock(OutgoingFrame::class);

        $this->parser->feed->returns([
            $this->incomingFrame1->mock(),
            $this->incomingFrame2->mock(),
        ]);

        $this->serializer->serialize->returns('<serialized-frame>');

        $this->subject = new StreamTransport(
            $this->stream->mock(),
            $this->parser->mock(),
            $this->serializer->mock()
        );
    }

    public function testConstructorPausesStream()
    {
        $this->stream->pause->called();
    }

    public function testResume()
    {
        $this->subject->resume(
            $this->controller->mock()
        );

        $this->stream->on->calledWith('data',  [$this->subject, 'onStreamData']);
        $this->stream->on->calledWith('error', [$this->subject, 'onStreamError']);
        $this->stream->on->calledWith('close', [$this->subject, 'onStreamClosed']);

        $this->stream->write->calledWith("AMQP\x00\x00\x09\x01");
        $this->stream->resume->called();
    }

    public function testResumeOnlyInitializesOnce()
    {
        $this->subject->resume(
            $this->controller->mock()
        );

        $this->subject->resume(
            $this->controller->mock()
        );

        $this->stream->on->thrice()->called();
        $this->stream->write->once()->called();
    }

    public function testPause()
    {
        $this->subject->pause();

        $this->stream->pause->called();
    }

    public function testSend()
    {
        $this->subject->send(
            $this->outgoingFrame->mock()
        );

        $this->serializer->serialize->calledWith(
            $this->outgoingFrame->mock()
        );

        $this->stream->write->calledWith(
            '<serialized-frame>'
        );
    }

    public function testClose()
    {
        $this->subject->close();

        $this->stream->close->called();
    }

    public function testOnStreamData()
    {
        $this->subject->resume(
            $this->controller->mock()
        );

        $this->subject->onStreamData('<incoming-data>');

        Phony::inOrder(
            $this->controller->onFrame->calledWith(
                $this->incomingFrame1->mock()
            ),
            $this->controller->onFrame->calledWith(
                $this->incomingFrame2->mock()
            )
        );
    }

    public function testOnStreamDataWithParserException()
    {
        $exception = new Exception('The exception!');

        $this->parser->feed->throws($exception);

        $this->subject->resume(
            $this->controller->mock()
        );

        $this->subject->onStreamData('<incoming-data>');

        Phony::inOrder(
            $this->controller->onTransportClosed->calledWith($exception),
            $this->stream->close->called()
        );
    }

    public function testOnStreamDataWithControllerException()
    {
        $exception = new Exception('The exception!');

        $this->controller->onFrame->throws($exception);

        $this->subject->resume(
            $this->controller->mock()
        );

        $this->subject->onStreamData('<incoming-data>');

        Phony::inOrder(
            $this->controller->onTransportClosed->calledWith($exception),
            $this->stream->close->called()
        );
    }

    public function testOnStreamError()
    {
        $exception = new Exception('The exception!');

        $this->subject->resume(
            $this->controller->mock()
        );

        $this->subject->onStreamError($exception);

        $this->controller->onTransportClosed->calledWith($exception);
    }

    public function testOnStreamClosed()
    {
        $this->subject->resume(
            $this->controller->mock()
        );

        $this->subject->onStreamClosed();

        $this->controller->onTransportClosed->calledWith(null);
    }
}
