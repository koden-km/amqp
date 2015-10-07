<?php
namespace Recoil\Amqp;

use Exception;
use PHPUnit_Framework_TestCase;

class ResourceNotFoundExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testQueueNotFound()
    {
        $previous = new Exception();
        $exception = ResourceNotFoundException::queueNotFound(
            '<name>',
            $previous
        );

        $this->assertSame(
            'Queue "<name>" does not exist.',
            $exception->getMessage()
        );

        $this->assertSame(
            $previous,
            $exception->getPrevious()
        );
    }
}
