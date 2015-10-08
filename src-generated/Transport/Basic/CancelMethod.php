<?php
namespace Recoil\Amqp\Transport\Basic;

final class CancelMethod
{
    public $consumerTag; // shortstr
    public $nowait; // bit
}
