<?php
namespace Recoil\Amqp\Transport\Connection;

final class StartOkMethod
{
    public $clientProperties; // table
    public $mechanism; // shortstr
    public $response; // longstr
    public $locale; // shortstr
}
