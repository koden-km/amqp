<?php

namespace Recoil\Amqp\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class ResourceLockedExceptionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->previous = new Exception();
    }

    public function testQueueIsExclusive()
    {
        $exception = ResourceLockedException::queueIsExclusive(
            '<name>',
            $this->previous
        );

        $this->commonAssertions($exception);

        $this->assertSame(
            'Failed to declare queue "<name>", another connection has exclusive access.',
            $exception->getMessage()
        );
    }

    public function testQueueHasExclusiveConsumer()
    {
        $exception = ResourceLockedException::queueHasExclusiveConsumer(
            '<name>',
            $this->previous
        );

        $this->commonAssertions($exception);

        $this->assertSame(
            'Failed to consume from queue "<name>", another connection has an exclusive consumer.',
            $exception->getMessage()
        );
    }

    private function commonAssertions($exception)
    {
        $this->assertInstanceOf(
            ResourceLockedException::class,
            $exception
        );

        $this->assertSame(
            $this->previous,
            $exception->getPrevious()
        );
    }
}
