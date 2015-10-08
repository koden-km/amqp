<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class GetEmptyFrame implements IncomingFrame
{
    public $channel;
    public $clusterId; // shortstr

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitBasicGetEmptyFrame($this);
    }
}
