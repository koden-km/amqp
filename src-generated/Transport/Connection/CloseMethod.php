<?php
namespace Recoil\Amqp\Transport\Connection;

final class CloseMethod
{
    public $replyCode; // short
    public $replyText; // shortstr
    public $classId; // short
    public $methodId; // short
}
