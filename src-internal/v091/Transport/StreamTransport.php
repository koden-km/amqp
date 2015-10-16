<?php

namespace Recoil\Amqp\v091\Transport;

use Exception;
use React\Stream\DuplexStreamInterface;
use Recoil\Amqp\v091\Protocol\Debug;
use Recoil\Amqp\v091\Protocol\FrameParser;
use Recoil\Amqp\v091\Protocol\FrameSerializer;
use Recoil\Amqp\v091\Protocol\GeneratedFrameParser;
use Recoil\Amqp\v091\Protocol\GeneratedFrameSerializer;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

/**
 * Send and receive AMQP frames over a bidirectional stream.
 */
final class StreamTransport implements Transport
{
    /**
     * @param DuplexStreamInterface $stream     The stream used for communication.
     * @param FrameParser|null      $parser     Parses incoming stream data into frames, null to use the default.
     * @param FrameSerializer|null  $serializer Serializes frames to be sent over the stream, null to use the default.
     */
    public function __construct(
        DuplexStreamInterface $stream,
        FrameParser $parser = null,
        FrameSerializer $serializer = null
    ) {
        $this->stream = $stream;
        $this->parser = $parser ?: new GeneratedFrameParser();
        $this->serializer = $serializer ?: new GeneratedFrameSerializer();

        $this->stream->pause();
    }

    /**
     * Resume (or start) listening to transport events.
     *
     * @param TransportController $controller The controller that is managing the transport.
     */
    public function resume(TransportController $controller)
    {
        if (null === $this->controller) {
            $this->stream->on('data',  [$this, 'onStreamData']);
            $this->stream->on('error', [$this, 'onStreamError']);
            $this->stream->on('close', [$this, 'onStreamClosed']);
            $this->stream->write(self::PROTOCOL_HEADER);
        }

        $this->controller = $controller;
        $this->stream->resume();
    }

    /**
     * Temporarily stop listening to transport events.
     */
    public function pause()
    {
        $this->stream->pause();
    }

    /**
     * Send a frame.
     *
     * @param OutgoingFrame $frame The frame to send.
     */
    public function send(OutgoingFrame $frame)
    {
        // @codeCoverageIgnoreStart
        if (Debug::ENABLED) {
            Debug::dumpOutgoingFrame($frame);
        }
        // @codeCoverageIgnoreEnd

        $this->stream->write(
            $this->serializer->serialize($frame)
        );
    }

    /**
     * Permanently stop listening to transport events and close the transport.
     */
    public function close()
    {
        $this->stream->close();
    }

    /**
     * The stream 'data' event handler.
     *
     * @access private
     *
     * @param string $buffer
     */
    public function onStreamData($buffer)
    {
        try {
            foreach ($this->parser->feed($buffer) as $frame) {
                // @codeCoverageIgnoreStart
                if (Debug::ENABLED) {
                    Debug::dumpIncomingFrame($frame);
                }
                // @codeCoverageIgnoreEnd

                $this->controller->onFrame($frame);
            }
        } catch (Exception $e) {
            $this->controller->onTransportClosed($e);
            $this->controller = null;
            $this->stream->close();
        }
    }

    /**
     * The stream 'error' event handler.
     *
     * @param Exception $exception
     *
     * @access private
     */
    public function onStreamError(Exception $exception)
    {
        $this->controller->onTransportClosed($exception);
        $this->controller = null;
    }

    /**
     * The stream 'close' event handler.
     *
     * @access private
     */
    public function onStreamClosed()
    {
        if ($this->controller) {
            $this->controller->onTransportClosed();
            $this->controller = null;
        }
    }

    /**
     * The AMQP protocol header is sent by the client before any frame-based
     * communication takes place.  It is the only data transferred that is not
     * a frame.
     */
    const PROTOCOL_HEADER = "AMQP\x00\x00\x09\x01";

    /**
     * @var DuplexStreamInterface The stream used for communication.
     */
    private $stream;

    /**
     * @var FrameParser The parser used to construct frames from incoming stream data.
     */
    private $parser;

    /**
     * @var FrameSerializer The serializer used to convert frames in to outgoing stream data.
     */
    private $serializer;

    /**
     * @var TransportController|null The controller that is notified of transport related events.
     */
    private $controller;
}
