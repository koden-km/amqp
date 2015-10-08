<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class QosFrame extends Frame
{
    public $prefetchSize; // long
    public $prefetchCount; // short
    public $global; // bit
}
