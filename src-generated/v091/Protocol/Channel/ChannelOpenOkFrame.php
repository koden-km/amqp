<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class ChannelOpenOkFrame implements IncomingFrame
{
    public $channel;
    public $channelId; // longstr

    public static function create(
        $channel = 0
      , $channelId = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->channelId = null === $channelId ? '' : $channelId;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingChannelOpenOkFrame($this);
    }
}
