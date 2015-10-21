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

        $newSubject = $this->subject->setProductName(
            '<productName>'
        );

        $this->assertSame(
            '<productName>',
            $newSubject->productName()
        );

        $this->assertSame(
            PackageInfo::NAME,
            $this->subject->productName()
        );
    }

    public function testProductNameWithNoChange()
    {
        $newSubject = $this->subject->setProductName(
            PackageInfo::NAME
        );

        $this->assertSame(
            $this->subject,
            $newSubject
        );
    }

    public function testProductVersion()
    {
        $this->assertSame(
            PackageInfo::VERSION,
            $this->subject->productVersion()
        );

        $newSubject = $this->subject->setProductVersion(
            '<productVersion>'
        );

        $this->assertSame(
            '<productVersion>',
            $newSubject->productVersion()
        );

        $this->assertSame(
            PackageInfo::VERSION,
            $this->subject->productVersion()
        );
    }

    public function testProductVersionWithNoChange()
    {
        $newSubject = $this->subject->setProductVersion(
            PackageInfo::VERSION
        );

        $this->assertSame(
            $this->subject,
            $newSubject
        );
    }

    public function testConnectionTimeout()
    {
        $this->assertNull(
            $this->subject->connectionTimeout()
        );

        $newSubject = $this->subject->setConnectionTimeout(
            120
        );

        $this->assertSame(
            120,
            $newSubject->connectionTimeout()
        );

        $this->assertNull(
            $this->subject->connectionTimeout()
        );
    }

    public function testConnectionTimeoutWithNoChange()
    {
        $newSubject = $this->subject->setConnectionTimeout(
            null
        );

        $this->assertSame(
            $this->subject,
            $newSubject
        );
    }

    public function testHeartbeatTimeout()
    {
        $this->assertNull(
            $this->subject->heartbeatTimeout()
        );

        $newSubject = $this->subject->setHeartbeatTimeout(
            120
        );

        $this->assertSame(
            120,
            $newSubject->heartbeatTimeout()
        );

        $this->assertNull(
            $this->subject->heartbeatTimeout()
        );
    }

    public function testHeartbeatTimeoutWithNoChange()
    {
        $newSubject = $this->subject->setHeartbeatTimeout(
            null
        );

        $this->assertSame(
            $this->subject,
            $newSubject
        );
    }
}
