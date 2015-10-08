<?php
namespace Recoil\Amqp\Protocol\Channel;

use Recoil\Amqp\Protocol\Frame;

final class OpenFrame extends Frame
{
    public $outOfBand; // shortstr
}
