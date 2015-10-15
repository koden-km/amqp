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
     * @return mixed<Frame>      The parsed frame, or null if there is not enough data to produce a frame.
     * @throws ProtocolException if the buffer is malformed.
     */
    public function feed($buffer);
}
