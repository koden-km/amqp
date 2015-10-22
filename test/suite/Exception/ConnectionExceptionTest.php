<?php

namespace Recoil\Amqp\Exception;

use Exception;
use PHPUnit_Framework_TestCase;
use Recoil\Amqp\ConnectionOptions;

class ConnectionExceptionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->options = ConnectionOptions::create();
        $this->previous = new Exception();
    }

    public function testCouldNotConnect()
    {
        $exception = ConnectionException::couldNotConnect(
            $this->options,
            '<description>',
            $this->previous
        );

        $this->assertSame(
            'Unable to connect to AMQP server [localhost:5672], check connection options and network connectivity (<description>).',
            $exception->getMessage()
        );

        $this->commonAssertions($exception);
    }

    public function testNotOpen()
    {
        $exception = ConnectionException::notOpen(
            $this->options,
            $this->previous
        );

        $this->assertSame(
            'Unable to use connection to AMQP server [localhost:5672] because it is closed.',
            $exception->getMessage()
        );

        $this->commonAssertions($exception);
    }

    public function testHandshakeFailed()
    {
        $exception = ConnectionException::handshakeFailed(
            $this->options,
            '<description>',
            $this->previous
        );

        $this->commonAssertions($exception);

        $this->assertSame(
            'Unable to complete handshake on AMQP server [localhost:5672], <description>.',
            $exception->getMessage()
        );
    }

    public function testAuthenticationFailed()
    {
        $exception = ConnectionException::authenticationFailed(
            $this->options,
            $this->previous
        );

        $this->assertSame(
            'Unable to authenticate as "guest" on AMQP server [localhost:5672], check authentication credentials.',
            $exception->getMessage()
        );

        $this->commonAssertions($exception);
    }

    public function testAuthorizationFailed()
    {
        $exception = ConnectionException::authorizationFailed(
            $this->options,
            $this->previous
        );

        $this->assertSame(
            'Unable to access vhost "/" as "guest" on AMQP server [localhost:5672], check permissions.',
            $exception->getMessage()
        );

        $this->commonAssertions($exception);
    }

    public function testHeartbeatTimedOut()
    {
        $exception = ConnectionException::heartbeatTimedOut(
            $this->options,
            580,
            $this->previous
        );

        $this->commonAssertions($exception);

        $this->assertSame(
            'The AMQP connection with server [localhost:5672] has timed out, the last heartbeat was received over 580 seconds ago.',
            $exception->getMessage()
        );
    }

    public function testClosedUnexpectedly()
    {
        $exception = ConnectionException::closedUnexpectedly(
            $this->options,
            $this->previous
        );

        $this->commonAssertions($exception);

        $this->assertSame(
            'The AMQP connection with server [localhost:5672] was closed unexpectedly.',
            $exception->getMessage()
        );
    }

    private function commonAssertions($exception)
    {
        $this->assertInstanceOf(
            ConnectionException::class,
            $exception
        );

        $this->assertSame(
            $this->options,
            $exception->connectionOptions()
        );

        $this->assertSame(
            $this->previous,
            $exception->getPrevious()
        );
    }
}
