<?php
namespace Recoil\Amqp\Transport\Connection;

final class TuneMethod
{
    public $channelMax; // short
    public $frameMax; // long
    public $heartbeat; // short
}
