<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class ConnectionStartOkFrame implements OutgoingFrame
{
    public $channel;
    public $clientProperties; // table
    public $mechanism; // shortstr
    public $response; // longstr
    public $locale; // shortstr

    public static function create(
        $channel = 0
      , $clientProperties = null
      , $mechanism = null
      , $response = null
      , $locale = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->clientProperties = null === $clientProperties ? [] : $clientProperties;
        $frame->mechanism = null === $mechanism ? 'PLAIN' : $mechanism;
        $frame->response = $response;
        $frame->locale = null === $locale ? 'en_US' : $locale;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingConnectionStartOkFrame($this);
    }
}
