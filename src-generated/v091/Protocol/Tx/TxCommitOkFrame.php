<?php
namespace Recoil\Amqp\v091\Protocol\Tx;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class TxCommitOkFrame implements IncomingFrame
{
    public $frameChannelId;

    public static function create(
        $frameChannelId = 0
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;

        return $frame;
    }
}
