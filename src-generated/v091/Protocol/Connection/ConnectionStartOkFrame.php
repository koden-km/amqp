<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ConnectionStartOkFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $clientProperties; // table
    public $mechanism; // shortstr
    public $response; // longstr
    public $locale; // shortstr

    public static function create(
        $frameChannelId = 0
      , $clientProperties = null
      , $mechanism = null
      , $response = null
      , $locale = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->clientProperties = null === $clientProperties ? [] : $clientProperties;
        $frame->mechanism = null === $mechanism ? 'PLAIN' : $mechanism;
        $frame->response = $response;
        $frame->locale = null === $locale ? 'en_US' : $locale;

        return $frame;
    }
}
