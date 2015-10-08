<?php
namespace Recoil\Amqp\Transport\Connection;

final class TuneOkMethod
{
    public $channelMax; // short
    public $frameMax; // long
    public $heartbeat; // short
}
