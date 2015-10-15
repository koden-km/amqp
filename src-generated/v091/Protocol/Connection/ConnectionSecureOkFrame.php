<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ConnectionSecureOkFrame implements OutgoingFrame
{
    public $channel;
    public $response; // longstr

    public static function create(
        $channel = 0
      , $response = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->response = $response;

        return $frame;
    }
}
