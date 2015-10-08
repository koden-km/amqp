<?php
namespace Recoil\Amqp\Transport\Queue;

final class DeclareOkMethod
{
    public $queue; // shortstr
    public $messageCount; // long
    public $consumerCount; // long
}
