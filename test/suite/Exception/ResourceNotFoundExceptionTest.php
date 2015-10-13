<?php

namespace Recoil\Amqp\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class ResourceNotFoundExceptionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->previous = new Exception();
    }

    public function testQueueNotFound()
    {
        $exception = ResourceNotFoundException::queueNotFound(
            '<name>',
            $this->previous
        );

        $this->commonAssertions($exception);

        $this->assertSame(
            'Queue "<name>" does not exist.',
            $exception->getMessage()
        );
    }

    private function commonAssertions($exception)
    {
        $this->assertInstanceOf(
            ResourceNotFoundException::class,
            $exception
        );

        $this->assertSame(
            $this->previous,
            $exception->getPrevious()
        );
    }
}
