<?php
namespace Recoil\Amqp\CodeGen;

interface Generator
{
    /**
     * Generate code based on the AMQP specification.
     *
     * @param GeneratorEngine $engine
     * @param object $spec
     *
     * @return mixed<string, string> A map of filename to code.
     */
    public function generate(GeneratorEngine $engine, $spec);
}
