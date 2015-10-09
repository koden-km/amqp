<?php
namespace Recoil\Amqp\Protocol\v091\Connection;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class OpenFrame implements OutgoingFrame
{
    public $channel;
    public $virtualHost; // shortstr
    public $capabilities; // shortstr
    public $insist; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConnectionOpenFrame($this);
    }
}
