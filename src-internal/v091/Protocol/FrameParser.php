<?php

namespace Recoil\Amqp\v091\Protocol;

use Recoil\Amqp\Exception\ProtocolException;

/**
 * Produces Frame objects from binary data.
 */
interface FrameParser
{
    /**
     * Retrieve the next frame from the internal buffer.
     *
     * @param string $buffer Binary data to feed to the parser.
     *
     * @return mixed<Frame>      A sequence of frames produced from the buffer.
     * @throws ProtocolException if the incoming data does not conform to the
     *                           AMQP specification.
     */
    public function feed($buffer);
}
