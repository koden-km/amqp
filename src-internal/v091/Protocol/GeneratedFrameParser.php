<?php

namespace Recoil\Amqp\v091\Protocol;

use Recoil\Amqp\Exception\ProtocolException;

/**
 * Produces Frame objects from binary data.
 *
 * Most of this class' logic is in {@see FrameParserTrait} which is generated
 * by {@see FrameParserTraitGenerator}.
 */
final class GeneratedFrameParser implements FrameParser
{
    /**
     * @param TableParser $tableParser The parser used to parse AMQP tables.
     */
    public function __construct(TableParser $tableParser)
    {
        $this->tableParser = $tableParser;
        $this->requiredBytes = self::MINIMUM_FRAME_SIZE;
        $this->buffer = '';
    }

    /**
     * Retrieve the next frame from the internal buffer.
     *
     * @param string $buffer Binary data to feed to the parser.
     *
     * @return mixed<Frame>      A sequence of frames produced from the buffer.
     * @throws ProtocolException if the incoming data does not conform to the
     *                           AMQP specification.
     */
    public function feed($buffer)
    {
        $this->buffer .= $buffer;

        while (true) {
            $availableBytes = strlen($this->buffer);

            // not enough bytes for a frame ...
            if ($availableBytes < $this->requiredBytes) {
                break;

            // we're still looking for the header ...
            } elseif ($this->requiredBytes === self::MINIMUM_FRAME_SIZE) {
                // now that we know the payload size we can add that to the number
                // of required bytes ...
                $this->requiredBytes += unpack(
                    'N',
                    substr(
                        $this->buffer,
                        self::HEADER_TYPE_SIZE + self::HEADER_CHANNEL_SIZE,
                        self::HEADER_PAYLOAD_LENGTH_SIZE
                    )
                )[1];

                // taking the payload into account we don't have enough bytes
                // for the frame ...
                if ($availableBytes < $this->requiredBytes) {
                    break;
                }
            }

            // we've got enough bytes, check that the last byte matches the end
            // marker ...
            if (Constants::FRAME_END !== ord($this->buffer[$this->requiredBytes - 1])) {
                throw ProtocolException::create(
                    sprintf(
                        'Frame end marker (0x%02x) is invalid.',
                        ord($this->buffer[$this->requiredBytes - 1])
                    )
                );
            }

            // read the (t)ype and (c)hannel then discard the header ...
            $fields = unpack('Ct/nc', $this->buffer);
            $this->buffer = substr($this->buffer, self::HEADER_SIZE);

            $type = $fields['t'];

            // read the frame ...
            if (Constants::FRAME_METHOD === $type) {
                $frame = $this->parseMethodFrame();
            } elseif (Constants::FRAME_HEADER === $type) {
                $frame = $this->parseContentHeaderFrame();
            } elseif (Constants::FRAME_BODY === $type) {
                $frame = $this->parseContentBodyFrame();
            } elseif (Constants::FRAME_HEARTBEAT === $type) {
                if (self::MINIMUM_FRAME_SIZE !== $this->requiredBytes) {
                    throw ProtocolException::create(
                        sprintf(
                            'Heartbeat frame payload size (%d) is invalid, must be zero.',
                            $this->requiredBytes - self::MINIMUM_FRAME_SIZE
                        )
                    );
                }
                $frame = new HeartbeatFrame(); // call constructor directly (perf)
            } else {
                throw ProtocolException::create(
                    sprintf(
                        'Frame type (0x%02x) is invalid.',
                        $type
                    )
                );
            }

            $this->buffer = substr($this->buffer, 1);

            $consumedBytes = $availableBytes - strlen($this->buffer);

            // the frame lied about its payload size ...
            if ($consumedBytes !== $this->requiredBytes) {
                throw ProtocolException::create(
                    sprintf(
                        'Mismatch between frame size (%s) and consumed bytes (%s).',
                        $this->requiredBytes,
                        $consumedBytes
                    )
                );
            }

            $this->requiredBytes = self::MINIMUM_FRAME_SIZE;

            $frame->channel = $fields['c'];

            yield $frame;
        }
    }

    use ScalarParserTrait;
    use FrameParserTrait;

    // the size of each portion of the header ...
    const HEADER_TYPE_SIZE           = 1; // header field "frame type" - unsigned octet
    const HEADER_CHANNEL_SIZE        = 2; // header field "channel id" - unsigned short
    const HEADER_PAYLOAD_LENGTH_SIZE = 4; // header field "payload length" - unsigned long

    // the total header size ...
    const HEADER_SIZE = self::HEADER_TYPE_SIZE
                      + self::HEADER_CHANNEL_SIZE
                      + self::HEADER_PAYLOAD_LENGTH_SIZE;

    // minimum size of a valid frame (header + end with no payload) ...
    const MINIMUM_FRAME_SIZE = self::HEADER_SIZE + 1; // end marker is always 1 byte

    /**
     * @var TableParser The parser used to parse AMQP tables.
     */
    private $tableParser;

    /**
     * @var integer The number of bytes required in the buffer to produce the
     *              next frame.
     *
     * This value starts as MINIMUM_FRAME_SIZE and is increased to include the
     * frame's payload size when the frame header becomes available.
     */
    private $requiredBytes;

    /**
     * @var string A buffer containing incoming binary data that can not yet be
     *             used to produce a frame.
     */
    private $buffer;
}
