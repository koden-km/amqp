<?php
namespace Recoil\Amqp\Transport\Basic;

final class GetMethod
{
    public $reserved; // short
    public $queue; // shortstr
    public $noAck; // bit
}
