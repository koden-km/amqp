<?php
namespace Recoil\Amqp\Protocol\v091\Connection;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class OpenOkFrame implements IncomingFrame
{
    public $channel;
    public $knownHosts; // shortstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConnectionOpenOkFrame($this);
    }
}
