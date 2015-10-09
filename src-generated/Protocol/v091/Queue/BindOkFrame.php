<?php
namespace Recoil\Amqp\Protocol\v091\Queue;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class BindOkFrame implements IncomingFrame
{
    public $channel;

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitQueueBindOkFrame($this);
    }
}
