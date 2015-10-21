<?php

namespace Recoil\Amqp\CodeGen;

use PHPUnit_Framework_TestCase;

/**
 * This test is really just used to find any unused logic paths in the generator
 * code, as the test suite doesn't even run unless the code generation works.
 *
 * The actual logic of the generated code is covered by integration tests (@todo).
 */
class CodeGenerationTest extends PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $this->expectOutputRegex('/.+/');

        CodeGeneratorEngine::run();
    }
}
