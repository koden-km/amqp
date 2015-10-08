<?php
namespace Recoil\Amqp\Transport\Queue;

final class PurgeMethod
{
    public $reserved; // short
    public $queue; // shortstr
    public $nowait; // bit
}
