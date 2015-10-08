<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\Frame;

final class SecureOkFrame extends Frame
{
    public $response; // longstr
}
