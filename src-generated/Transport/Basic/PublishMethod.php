<?php
namespace Recoil\Amqp\Transport\Basic;

final class PublishMethod
{
    public $reserved; // short
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $mandatory; // bit
    public $immediate; // bit
}
