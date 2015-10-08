<?php
namespace Recoil\Amqp\Transport\Access;

final class RequestMethod
{
    public $realm; // shortstr
    public $exclusive; // bit
    public $passive; // bit
    public $active; // bit
    public $write; // bit
    public $read; // bit
}
