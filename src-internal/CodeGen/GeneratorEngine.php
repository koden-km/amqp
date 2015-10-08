<?php
namespace Recoil\Amqp\CodeGen;

use LogicException;
use Traversable;

/**
 * @codeCoverageIgnore
 */
final class GeneratorEngine
{
    public function __construct($spec, $path)
    {
        $this->spec = json_decode(file_get_contents($spec));
        $this->path = rtrim($path, '/');
        $this->generators = [];
    }

    public function add(Generator $generator)
    {
        $this->generators[] = $generator;
    }

    public function generate()
    {
        $this->delete($this->path);

        foreach ($this->generators as $generator) {
            $files = $generator->generate($this, $this->spec);

            foreach ($files as $name => $content) {
                $name = $this->path . '/' . $name;

                @mkdir(
                    dirname($name),
                    0777,
                    true
                );

                $fp = fopen($name, 'w');
                $this->write($fp, $content);
                fclose($fp);
            }
        }
    }

    private function write($fp, $data)
    {
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
        foreach (scandir($path) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $p = $path . '/' . $entry;

            if (is_dir($p)) {
                $this->delete($p);
            } else {
                unlink($p);
            }
        }
    }

    public function resolveArgumentType($argument)
    {
        if (isset($argument->domain)) {
            foreach ($this->spec->domains as list($domain, $type)) {
                if ($domain === $argument->domain) {
                    return $type;
                }
            }
        }

        return $argument->type;
    }

    public function isFixedSize($type)
    {
        try {
            $this->sizeInBytes($type);
        } catch (LogicException $e) {
            return false;
        }

        return true;
    }

    public function sizeInBytes($type)
    {
        static $types = [
            'octet'     => 1,
            'short'     => 2,
            'long'      => 4,
            'longlong'  => 8,
            'timestamp' => 8,
        ];

        if (isset($types[$type])) {
            return $types[$type];
        }

        throw new LogicException('Not a fixed-length type: ' . $type . '.');
    }

    public function unpackFormat($type, $name = '')
    {
        static $formats = [
            'octet'     => 'c',
            'short'     => 'n',
            'long'      => 'N',
            'longlong'  => 'J',
            'timestamp' => 'J',
        ];

        if (isset($formats[$type])) {
            return $formats[$type] . $name;
        }

        throw new LogicException('No unpack format for type: ' . $type . '.');
    }

    private $spec;
    private $path;
    private $generators;
}
