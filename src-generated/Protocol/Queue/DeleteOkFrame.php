<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\Frame;

final class DeleteOkFrame extends Frame
{
    public $messageCount; // long
}
