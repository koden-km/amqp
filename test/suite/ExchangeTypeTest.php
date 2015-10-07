<?php
namespace Recoil\Amqp;

use PHPUnit_Framework_TestCase;

class ExchangeTypeTest extends PHPUnit_Framework_TestCase
{
    public function requiresRoutingKey()
    {
        yield [ExchangeType::DIRECT,  true];
        yield [ExchangeType::FANOUT,  false];
        yield [ExchangeType::TOPIC,   true];
        yield [ExchangeType::HEADERS, false];
    }
    /**
     * @dataProvider requiresRoutingKey
     */
    public function testRequiresRoutingKey($member, $requiresRoutingKey)
    {
        $this->assertSame(
            $requiresRoutingKey,
            ExchangeType::memberByValue($member)->requiresRoutingKey()
        );
    }
}
