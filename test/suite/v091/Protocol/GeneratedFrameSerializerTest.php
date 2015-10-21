<?php

namespace Recoil\Amqp\v091\Protocol;

use Eloquent\Phony\Phpunit\Phony;
use PHPUnit_Framework_TestCase;

class GeneratedFrameSerializerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tableSerializer = Phony::fullMock(TableSerializer::class);

        $this->subject = new GeneratedFrameSerializer(
            $this->tableSerializer->mock()
        );
    }

    public function testXXX()
    {
        $this->markTestIncomplete();
    }
}
