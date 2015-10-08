<?php
namespace Recoil\Amqp\Protocol\Access;

use Recoil\Amqp\Protocol\Frame;

final class RequestOkFrame extends Frame
{
    public $reserved; // short
}
