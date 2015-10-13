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
            $this->previous
        );

        $this->assertSame(
            'Unable to connect to AMQP server [localhost:5672], check connection options and network connectivity.',
            $exception->getMessage()
        );

        $this->sharedAssertions($exception);
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

        $this->sharedAssertions($exception);
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

        $this->sharedAssertions($exception);
    }

    public function testHeartbeatTimedOut()
    {
        $exception = ConnectionException::heartbeatTimedOut(
            $this->options,
            580,
            $this->previous
        );

        $this->assertSame(
            'The AMQP connection with server [localhost:5672] has timed out, no heartbeat received for over 580 seconds.',
            $exception->getMessage()
        );

        $this->sharedAssertions($exception);
    }

    public function testClosedUnexpectedly()
    {
        $exception = ConnectionException::closedUnexpectedly(
            $this->options,
            $this->previous
        );

        $this->assertSame(
            'The AMQP connection with server [localhost:5672] was closed unexpectedly.',
            $exception->getMessage()
        );

        $this->sharedAssertions($exception);
    }

    private function sharedAssertions(ConnectionException $exception)
    {
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
