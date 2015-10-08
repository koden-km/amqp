<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\Frame;

final class DeclareOkFrame extends Frame
{
    public $queue; // shortstr
    public $messageCount; // long
    public $consumerCount; // long
}
