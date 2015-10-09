<?php
namespace Recoil\Amqp\Protocol\v091\Connection;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class SecureOkFrame implements OutgoingFrame
{
    public $channel;
    public $response; // longstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConnectionSecureOkFrame($this);
    }
}
