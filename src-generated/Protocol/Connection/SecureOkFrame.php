<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class SecureOkFrame implements OutgoingFrame
{
    public $channel;
    public $response; // longstr

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitConnectionSecureOkFrame($this);
    }
}
