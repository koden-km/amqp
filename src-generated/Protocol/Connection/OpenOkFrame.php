<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\Frame;

final class OpenOkFrame extends Frame
{
    public $knownHosts; // shortstr
}
