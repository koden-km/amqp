<?php
namespace Recoil\Amqp\Transport\Basic;

final class ReturnMethod
{
    public $replyCode; // short
    public $replyText; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr
}
