<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\Frame;

final class CloseFrame extends Frame
{
    public $replyCode; // short
    public $replyText; // shortstr
    public $classId; // short
    public $methodId; // short
}
