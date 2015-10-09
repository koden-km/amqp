<?php
namespace Recoil\Amqp\Protocol;

/**
 * Produces Frame objects from binary data.
 */
interface FrameParser
{
    /**
     * Feed binary data to the parser.
     *
     * @param string $buffer The binary data.
     *
     * @return mixed<Frame> A sequence of frames produces from the binary data.
     */
    public function feed($buffer);
}
