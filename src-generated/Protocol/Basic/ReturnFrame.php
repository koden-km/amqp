<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class ReturnFrame extends Frame
{
    public $replyCode; // short
    public $replyText; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr
}
