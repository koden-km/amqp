<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class GetEmptyFrame implements IncomingFrame
{
    public $channel;
    public $clusterId; // shortstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicGetEmptyFrame($this);
    }
}
