<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\Frame;

final class StartOkFrame extends Frame
{
    public $clientProperties; // table
    public $mechanism; // shortstr
    public $response; // longstr
    public $locale; // shortstr
}
