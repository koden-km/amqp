<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\Frame;

final class SecureFrame extends Frame
{
    public $challenge; // longstr
}
