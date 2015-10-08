<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\Frame;

final class TuneFrame extends Frame
{
    public $channelMax; // short
    public $frameMax; // long
    public $heartbeat; // short
}
