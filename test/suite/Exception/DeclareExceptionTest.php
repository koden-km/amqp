<?php

namespace Recoil\Amqp\Exception;

use Exception;
use PHPUnit_Framework_TestCase;
use Recoil\Amqp\ExchangeOptions;
use Recoil\Amqp\ExchangeType;
use Recoil\Amqp\QueueOptions;

class DeclareExceptionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->previous = new Exception();
    }

    public function testExchangeTypeOrOptionMismatch()
    {
        $exception = DeclareException::exchangeTypeOrOptionMismatch(
            '<name>',
            ExchangeType::DIRECT(),
            ExchangeOptions::internal(true),
            $this->previous
        );

        $this->commonAssertions($exception);

        $this->assertSame(
            'Failed to declare exchange "<name>", type "DIRECT" or options [internal] do not match the server.',
            $exception->getMessage()
        );
    }

    public function testQueueOptionMismatch()
    {
        $exception = DeclareException::queueOptionMismatch(
            '<name>',
            QueueOptions::defaults(),
            $this->previous
        );

        $this->commonAssertions($exception);

        $this->assertSame(
            'Failed to declare queue "<name>", options [exclusive] do not match the server.',
            $exception->getMessage()
        );
    }

    private function commonAssertions($exception)
    {
        $this->assertInstanceOf(
            DeclareException::class,
            $exception
        );

        $this->assertSame(
            $this->previous,
            $exception->getPrevious()
        );
    }
}
