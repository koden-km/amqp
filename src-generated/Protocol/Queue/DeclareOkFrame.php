<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class DeclareOkFrame implements IncomingFrame
{
    public $channel;
    public $queue; // shortstr
    public $messageCount; // long
    public $consumerCount; // long

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitQueueDeclareOkFrame($this);
    }
}
