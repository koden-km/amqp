<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class CancelOkFrame extends Frame
{
    public $consumerTag; // shortstr
}
