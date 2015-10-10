<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class ConnectionStartFrame implements IncomingFrame
{
    public $channel;
    public $versionMajor; // octet
    public $versionMinor; // octet
    public $serverProperties; // table
    public $mechanisms; // longstr
    public $locales; // longstr

    public static function create(
        $channel = 0
      , $versionMajor = null
      , $versionMinor = null
      , $serverProperties = null
      , $mechanisms = null
      , $locales = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->versionMajor = null === $versionMajor ? 0 : $versionMajor;
        $frame->versionMinor = null === $versionMinor ? 9 : $versionMinor;
        $frame->serverProperties = null === $serverProperties ? [] : $serverProperties;
        $frame->mechanisms = null === $mechanisms ? 'PLAIN' : $mechanisms;
        $frame->locales = null === $locales ? 'en_US' : $locales;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingConnectionStartFrame($this);
    }
}
