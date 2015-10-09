<?php
namespace Recoil\Amqp\Protocol\v091\Channel;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class OpenOkFrame implements IncomingFrame
{
    public $channel;
    public $channelId; // longstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitChannelOpenOkFrame($this);
    }
}
