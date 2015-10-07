<?php
namespace Recoil\Amqp\Transport;

final class Frame
{
    public $type;
    public $channel;
    public $payload;

    public function __construct($type, $channel, $payload)
    {
        $this->type = $type;
        $this->channel = $channel;
        $this->payload = $payload;
    }
}
