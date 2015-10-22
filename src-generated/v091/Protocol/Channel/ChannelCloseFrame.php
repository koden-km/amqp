<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ChannelCloseFrame implements IncomingFrame, OutgoingFrame
{
    public $frameChannelId;
    public $replyCode; // short
    public $replyText; // shortstr
    public $classId; // short
    public $methodId; // short

    public static function create(
        $frameChannelId = 0
      , $replyCode = null
      , $replyText = null
      , $classId = null
      , $methodId = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->replyCode = $replyCode;
        $frame->replyText = null === $replyText ? '' : $replyText;
        $frame->classId = $classId;
        $frame->methodId = $methodId;

        return $frame;
    }
}
