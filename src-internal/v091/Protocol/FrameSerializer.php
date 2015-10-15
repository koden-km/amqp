<?php

namespace Recoil\Amqp\v091\Protocol;

/**
 * Serializes frames to binary data.
 */
interface FrameSerializer
{
    /**
     * Serialize a frame, for transmission to the server.
     *
     * @param OutgoingFrame $frame The frame to serialize.
     *
     * @return string The binary serialized frame.
     */
    public function serialize(OutgoingFrame $frame);
}
