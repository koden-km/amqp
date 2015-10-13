<?php

namespace Recoil\Amqp\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class ProtocolExceptionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->previous = new Exception();
    }

    public function testCreate()
    {
        $exception = ProtocolException::create(
            '<description>',
            $this->previous
        );

        $this->commonAssertions($exception);

        $this->assertSame(
            'The AMQP server has sent invalid data (<description>).',
            $exception->getMessage()
        );
    }

    private function commonAssertions($exception)
    {
        $this->assertInstanceOf(
            ProtocolException::class,
            $exception
        );

        $this->assertSame(
            $this->previous,
            $exception->getPrevious()
        );
    }
}
