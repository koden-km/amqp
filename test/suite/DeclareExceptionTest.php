<?php
namespace Recoil\Amqp;

use Exception;
use Icecave\Flip\OptionSet;
use PHPUnit_Framework_TestCase;

class DeclareExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testExchangeTypeOrOptionMismatch()
    {
        $previous = new Exception();
        $exception = DeclareException::exchangeTypeOrOptionMismatch(
            '<name>',
            ExchangeType::DIRECT(),
            OptionSet::create(
                ExchangeOption::class,
                ExchangeOption::DURABLE(),
                ExchangeOption::AUTO_DELETE()
            ),
            $previous
        );

        $this->assertSame(
            'Failed to declare exchange "<name>", type "DIRECT" or options [DURABLE, AUTO_DELETE] do not match the server.',
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
            OptionSet::create(
                QueueOption::class,
                QueueOption::DURABLE(),
                QueueOption::AUTO_DELETE()
            ),
            $previous
        );

        $this->assertSame(
            'Failed to declare queue "<name>", options [DURABLE, AUTO_DELETE] do not match the server.',
            $exception->getMessage()
        );

        $this->assertSame(
            $previous,
            $exception->getPrevious()
        );
    }
}
