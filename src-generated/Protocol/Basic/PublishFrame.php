<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class PublishFrame implements OutgoingFrame
{
    public $channel;
    public $reserved; // short
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $mandatory; // bit
    public $immediate; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitBasicPublishFrame($this);
    }
}
