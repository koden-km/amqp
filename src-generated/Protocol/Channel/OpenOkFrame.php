<?php
namespace Recoil\Amqp\Protocol\Channel;

use Recoil\Amqp\Protocol\Frame;

final class OpenOkFrame extends Frame
{
    public $channelId; // longstr
}
