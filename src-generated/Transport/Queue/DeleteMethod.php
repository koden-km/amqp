<?php
namespace Recoil\Amqp\Transport\Queue;

final class DeleteMethod
{
    public $reserved; // short
    public $queue; // shortstr
    public $ifUnused; // bit
    public $ifEmpty; // bit
    public $nowait; // bit
}
