<?php
namespace Recoil\Amqp\Transport\Connection;

final class OpenMethod
{
    public $virtualHost; // shortstr
    public $capabilities; // shortstr
    public $insist; // bit
}
