<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class GetEmptyFrame extends Frame
{
    public $clusterId; // shortstr
}
