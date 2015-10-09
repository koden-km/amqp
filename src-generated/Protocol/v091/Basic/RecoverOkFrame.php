<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class RecoverOkFrame implements IncomingFrame
{
    public $channel;

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicRecoverOkFrame($this);
    }
}
