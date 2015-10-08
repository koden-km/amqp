<?php
namespace Recoil\Amqp\Protocol\Access;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class RequestOkFrame implements IncomingFrame
{
    public $channel;
    public $reserved; // short

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitAccessRequestOkFrame($this);
    }
}
