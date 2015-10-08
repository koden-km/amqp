<?php
namespace Recoil\Amqp\Protocol\Channel;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class OpenOkFrame implements IncomingFrame
{
    public $channel;
    public $channelId; // longstr

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitChannelOpenOkFrame($this);
    }
}
