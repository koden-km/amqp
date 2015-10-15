<?php

namespace Recoil\Amqp\v091\Protocol;

use PHPUnit_Framework_TestCase;

class HeartbeatFrameTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $frame = HeartbeatFrame::create();

        $this->assertInstanceOf(
            HeartbeatFrame::class,
            $frame
        );
    }

    public function testIsSingleton()
    {
        $frame = HeartbeatFrame::create();

        $this->assertSame(
            HeartbeatFrame::create(),
            HeartbeatFrame::create()
        );
    }
}
