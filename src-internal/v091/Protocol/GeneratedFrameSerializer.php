<?php

namespace Recoil\Amqp\v091\Protocol;

/**
 * Serializes frames to binary data.
 *
 * Most of this class' logic is in {@see FrameSerializerTrait} which is
 * generated by {@see FrameSerializerTraitGenerator}.
 */
final class GeneratedFrameSerializer implements FrameSerializer
{
    /**
     * @param TableSerializer $tableSerializer The serializer used to serialize AMQP tables.
     */
    public function __construct(TableSerializer $tableSerializer)
    {
        $this->tableSerializer = $tableSerializer;
    }

    /**
     * Serialize a heartbeat frame.
     *
     * @return string The serialized frame.
     */
    private function serializeHeartbeatFrame()
    {
        // Cache the heartbeat frame buffer, as they can never differ ...
        if (null === self::$heartbeatBuffer) {
            self::$heartbeatBuffer = chr(Constants::FRAME_HEARTBEAT)
                                   . "\x00\x00" // channel
                                   . "\x00\x00\x00\x00" // size
                                   . chr(Constants::FRAME_END);
        }

        return self::$heartbeatBuffer;
    }

    use ScalarSerializerTrait;
    use FrameSerializerTrait;

    /**
     * @var string The buffer for a serialized heartbeat frame.
     */
    private static $heartbeatBuffer;

    /**
     * @var TableSerializer The serializer used to serialize AMQP tables.
     */
    private $tableSerializer;
}
