<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class OpenFrame implements OutgoingFrame
{
    public $channel;
    public $virtualHost; // shortstr
    public $capabilities; // shortstr
    public $insist; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitConnectionOpenFrame($this);
    }
}
