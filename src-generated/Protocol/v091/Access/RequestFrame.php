<?php
namespace Recoil\Amqp\Protocol\v091\Access;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class RequestFrame implements OutgoingFrame
{
    public $channel;
    public $realm; // shortstr
    public $exclusive; // bit
    public $passive; // bit
    public $active; // bit
    public $write; // bit
    public $read; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitAccessRequestFrame($this);
    }
}
