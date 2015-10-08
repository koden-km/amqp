<?php
namespace Recoil\Amqp\Transport\Exchange;

final class DeleteMethod
{
    public $reserved; // short
    public $exchange; // shortstr
    public $ifUnused; // bit
    public $nowait; // bit
}
