<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class PublishFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $mandatory; // bit
    public $immediate; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicPublishFrame($this);
    }
}
