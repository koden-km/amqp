<?php

namespace Recoil\Amqp\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class DeclareExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testExchangeTypeOrOptionMismatch()
    {
        $previous = new Exception();
        $exception = DeclareException::exchangeTypeOrOptionMismatch(
            '<name>',
            ExchangeType::DIRECT(),
            ExchangeOptions::internal(true),
            $previous
        );

        $this->assertSame(
            'Failed to declare exchange "<name>", type "DIRECT" or options [internal] do not match the server.',
            $exception->getMessage()
        );

        $this->assertSame(
            $previous,
            $exception->getPrevious()
        );
    }

    public function testQueueOptionMismatch()
    {
        $previous = new Exception();
        $exception = DeclareException::queueOptionMismatch(
            '<name>',
            QueueOptions::defaults(),
            $previous
        );

        $this->assertSame(
            'Failed to declare queue "<name>", options [exclusive] do not match the server.',
            $exception->getMessage()
        );

        $this->assertSame(
            $previous,
            $exception->getPrevious()
        );
    }
}
