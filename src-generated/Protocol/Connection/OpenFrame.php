<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\Frame;

final class OpenFrame extends Frame
{
    public $virtualHost; // shortstr
    public $capabilities; // shortstr
    public $insist; // bit
}
