<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ConnectionSecureOkFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $response; // longstr

    public static function create(
        $frameChannelId = 0
      , $response = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->response = $response;

        return $frame;
    }
}
