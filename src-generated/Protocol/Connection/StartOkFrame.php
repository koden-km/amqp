<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class StartOkFrame implements OutgoingFrame
{
    public $channel;
    public $clientProperties; // table
    public $mechanism; // shortstr
    public $response; // longstr
    public $locale; // shortstr

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitConnectionStartOkFrame($this);
    }
}
