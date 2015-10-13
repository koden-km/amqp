<?php

namespace Recoil\Amqp\CodeGen;

/**
 * Generates AMQP code from specification (used in parser, serializer, etc).
 */
final class CodeGeneratorEngine
{
    public function __construct()
    {
        $this->generators = [
            new Generators\ConstantsGenerator(),
            new Generators\FrameVisitorGenerator(),
            new Generators\MethodFrameGenerator(),
            new Generators\MethodParserGenerator(),
            new Generators\MethodSerializerGenerator(),
        ];
    }

    /**
     * Generate code.
     *
     * @param string $filename The file containing the AMQP specification, in JSON format.
     * @param string $target   The output directory.
     */
    public function generate($filename, $target)
    {
        $spec    = json_decode(file_get_contents($filename));
        $version = 'v' . $spec->{'major-version'} . $spec->{'minor-version'} . $spec->revision;
        $target .= '/' . $version . '/Protocol';

        $this->delete($target);

        echo 'Generating files: ' . PHP_EOL;

        foreach ($this->generators as $generator) {
            $files = $generator->generate($version, $spec);

            foreach ($files as $name => $content) {
                echo ' * ' . $name . ' .';
                $name = $target . '/' . $name;

                @mkdir(
                    dirname($name),
                    0777,
                    true
                );

                $fp = fopen($name, 'w');
                $this->write($fp, $content);
                fclose($fp);

                echo PHP_EOL;
            }
        }
    }

    private function write($fp, $data)
    {
        echo '.';

        if (is_string($data)) {
            fwrite($fp, $data . PHP_EOL);
        } elseif (null === $data) {
            fwrite($fp, PHP_EOL);
        } else {
            foreach ($data as $d) {
                $this->write($fp, $d);
            }
        }
    }

    private function delete($path)
    {
        if (is_dir($path)) {
            foreach (scandir($path) as $entry) {
                if ($entry !== '.' && $entry !== '..') {
                    $this->delete($path . '/' . $entry);
                }
            }

            rmdir($path);
        } elseif (file_exists($path)) {
            unlink($path);
        }
    }

    private $generators;
}
