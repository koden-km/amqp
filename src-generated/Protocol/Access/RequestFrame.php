<?php
namespace Recoil\Amqp\Protocol\Access;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class RequestFrame implements OutgoingFrame
{
    public $channel;
    public $realm; // shortstr
    public $exclusive; // bit
    public $passive; // bit
    public $active; // bit
    public $write; // bit
    public $read; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitAccessRequestFrame($this);
    }
}
