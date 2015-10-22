<?php

namespace Recoil\Amqp;

use PHPUnit_Framework_TestCase;

class ConnectionOptionsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->subject = ConnectionOptions::create(
            '<host>',
            '<port>',
            '<username>',
            '<password>',
            '<vhost>'
        );
    }

    public function testHost()
    {
        $this->assertSame(
            '<host>',
            $this->subject->host()
        );
    }

    public function testPort()
    {
        $this->assertSame(
            '<port>',
            $this->subject->port()
        );
    }

    public function testUsername()
    {
        $this->assertSame(
            '<username>',
            $this->subject->username()
        );
    }

    public function testPassword()
    {
        $this->assertSame(
            '<password>',
            $this->subject->password()
        );
    }

    public function testVhost()
    {
        $this->assertSame(
            '<vhost>',
            $this->subject->vhost()
        );
    }

    public function testProductName()
    {
        $this->assertSame(
            PackageInfo::NAME,
            $this->subject->productName()
        );

        $options = $this->subject->setProductName(
            '<productName>'
        );

        $this->assertSame(
            '<productName>',
            $options->productName()
        );

        $this->assertSame(
            PackageInfo::NAME,
            $this->subject->productName()
        );
    }

    public function testProductNameWithNoChange()
    {
        $options = $this->subject->setProductName(
            PackageInfo::NAME
        );

        $this->assertSame(
            $this->subject,
            $options
        );
    }

    public function testProductVersion()
    {
        $this->assertSame(
            PackageInfo::VERSION,
            $this->subject->productVersion()
        );

        $options = $this->subject->setProductVersion(
            '<productVersion>'
        );

        $this->assertSame(
            '<productVersion>',
            $options->productVersion()
        );

        $this->assertSame(
            PackageInfo::VERSION,
            $this->subject->productVersion()
        );
    }

    public function testProductVersionWithNoChange()
    {
        $options = $this->subject->setProductVersion(
            PackageInfo::VERSION
        );

        $this->assertSame(
            $this->subject,
            $options
        );
    }

    public function testConnectionTimeout()
    {
        $this->assertNull(
            $this->subject->connectionTimeout()
        );

        $options = $this->subject->setConnectionTimeout(10);

        $this->assertSame(
            10,
            $options->connectionTimeout()
        );

        $this->assertNull(
            $this->subject->connectionTimeout()
        );
    }

    public function testConnectionTimeoutWithNoChange()
    {
        $options = $this->subject->setConnectionTimeout(
            null
        );

        $this->assertSame(
            $this->subject,
            $options
        );
    }

    public function testHeartbeatInterval()
    {
        $this->assertNull(
            $this->subject->heartbeatInterval()
        );

        $options = $this->subject->setHeartbeatInterval(60);

        $this->assertSame(
            60,
            $options->heartbeatInterval()
        );

        $this->assertNull(
            $this->subject->heartbeatInterval()
        );
    }

    public function testHeartbeatIntervalWithNoChange()
    {
        $options = $this->subject->setHeartbeatInterval(
            null
        );

        $this->assertSame(
            $this->subject,
            $options
        );
    }
}
