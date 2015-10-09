<?php
namespace Recoil\Amqp\Protocol\v091\Connection;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class SecureFrame implements IncomingFrame
{
    public $channel;
    public $challenge; // longstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConnectionSecureFrame($this);
    }
}
