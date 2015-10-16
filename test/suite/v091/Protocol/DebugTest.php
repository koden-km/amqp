<?php

namespace Recoil\Amqp\v091\Protocol;

use PHPUnit_Framework_TestCase;

class DebugTest extends PHPUnit_Framework_TestCase
{
    public function testDebugIsDisabled()
    {
        if (getenv('CI') === 'true') {
            $this->assertFalse(
                Debug::ENABLED,
                'Do not commit to the repository with debug enabled!'
            );
        } else {
            // Silence the Risky test warning ...
            $this->assertTrue(true);
        }
    }
}
