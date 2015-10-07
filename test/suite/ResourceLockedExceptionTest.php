<?php
namespace Recoil\Amqp;

use Exception;
use PHPUnit_Framework_TestCase;

class ResourceLockedExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testQueueIsExclusive()
    {
        $previous = new Exception();
        $exception = ResourceLockedException::queueIsExclusive(
            '<name>',
            $previous
        );

        $this->assertSame(
            'Failed to declare queue "<name>", another connection has exclusive access.',
            $exception->getMessage()
        );

        $this->assertSame(
            $previous,
            $exception->getPrevious()
        );
    }

    public function testQueueHasExclusiveConsumer()
    {
        $previous = new Exception();
        $exception = ResourceLockedException::queueHasExclusiveConsumer(
            '<name>',
            $previous
        );

        $this->assertSame(
            'Failed to consume from queue "<name>", another connection has an exclusive consumer.',
            $exception->getMessage()
        );

        $this->assertSame(
            $previous,
            $exception->getPrevious()
        );
    }
}
