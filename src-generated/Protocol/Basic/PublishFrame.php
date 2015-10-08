<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class PublishFrame extends Frame
{
    public $reserved; // short
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $mandatory; // bit
    public $immediate; // bit
}
