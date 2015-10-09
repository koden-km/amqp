<?php
namespace Recoil\Amqp\Protocol\v091\Access;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class RequestOkFrame implements IncomingFrame
{
    public $channel;
    public $reserved1; // short

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitAccessRequestOkFrame($this);
    }
}
