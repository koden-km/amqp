<?php
namespace Recoil\Amqp\Protocol;

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

    /**
     * Serialize a username and password suitable for use in the "response"
     * argument of a Start-Ok message when using AMQPLAIN authentication.
     *
     * @param string $username
     * @param string $password
     *
     * @return string The binary serialized frame.
     */
    public function serializePlainCredentials($username, $password);
}
