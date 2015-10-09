<?php
namespace Recoil\Amqp\Protocol\v091\Queue;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class DeclareOkFrame implements IncomingFrame
{
    public $channel;
    public $queue; // shortstr
    public $messageCount; // long
    public $consumerCount; // long

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitQueueDeclareOkFrame($this);
    }
}
