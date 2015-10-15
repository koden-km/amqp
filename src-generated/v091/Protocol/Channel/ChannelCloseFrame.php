<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ChannelCloseFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;
    public $replyCode; // short
    public $replyText; // shortstr
    public $classId; // short
    public $methodId; // short

    public static function create(
        $channel = 0
      , $replyCode = null
      , $replyText = null
      , $classId = null
      , $methodId = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->replyCode = $replyCode;
        $frame->replyText = null === $replyText ? '' : $replyText;
        $frame->classId = $classId;
        $frame->methodId = $methodId;

        return $frame;
    }
}
