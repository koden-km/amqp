<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class OpenOkFrame implements IncomingFrame
{
    public $channel;
    public $knownHosts; // shortstr

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitConnectionOpenOkFrame($this);
    }
}
