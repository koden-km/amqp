<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class ConsumeOkFrame extends Frame
{
    public $consumerTag; // shortstr
}
