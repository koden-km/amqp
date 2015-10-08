<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class StartFrame implements IncomingFrame
{
    public $channel;
    public $versionMajor; // octet
    public $versionMinor; // octet
    public $serverProperties; // table
    public $mechanisms; // longstr
    public $locales; // longstr

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitConnectionStartFrame($this);
    }
}
