<?php
namespace Recoil\Amqp;

use PHPUnit_Framework_TestCase;

class QueueOptionTest extends PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $expected = [
            QueueOption::EXCLUSIVE()->on(),
        ];

        $defaults = QueueOption::defaults();

        QueueOption::normalize($expected);
        QueueOption::normalize($defaults);

        $this->assertEquals(
            $expected,
            $defaults
        );
    }
}
