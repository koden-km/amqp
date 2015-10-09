<?php
namespace Recoil\Amqp\Protocol\v091\Connection;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class StartOkFrame implements OutgoingFrame
{
    public $channel;
    public $clientProperties; // table
    public $mechanism; // shortstr
    public $response; // longstr
    public $locale; // shortstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConnectionStartOkFrame($this);
    }
}
