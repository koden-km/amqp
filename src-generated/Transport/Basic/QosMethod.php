<?php
namespace Recoil\Amqp\Transport\Basic;

final class QosMethod
{
    public $prefetchSize; // long
    public $prefetchCount; // short
    public $global; // bit
}
