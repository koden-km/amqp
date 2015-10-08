<?php
namespace Recoil\Amqp\Protocol\Access;

use Recoil\Amqp\Protocol\Frame;

final class RequestFrame extends Frame
{
    public $realm; // shortstr
    public $exclusive; // bit
    public $passive; // bit
    public $active; // bit
    public $write; // bit
    public $read; // bit
}
