<?php
namespace Recoil\Amqp\Protocol\v091\Connection;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class StartFrame implements IncomingFrame
{
    public $channel;
    public $versionMajor; // octet
    public $versionMinor; // octet
    public $serverProperties; // table
    public $mechanisms; // longstr
    public $locales; // longstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConnectionStartFrame($this);
    }
}
