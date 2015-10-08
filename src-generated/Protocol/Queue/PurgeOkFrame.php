<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\Frame;

final class PurgeOkFrame extends Frame
{
    public $messageCount; // long
}
