<?php
namespace Recoil\Amqp\Protocol;

trait StringReaderTrait
{
    private function readShortString()
    {
        $length = ord($this->buffer);

        try {
            return substr($this->buffer, 1, $length);
        } finally {
            $this->buffer = substr($this->buffer, $length + 1);
        }
    }

    private function readLongString()
    {
        list(, $length) = unpack("N", $this->buffer);

        try {
            return substr($this->buffer, 4, $length);
        } finally {
            $this->buffer = substr($this->buffer, $length + 4);
        }
    }
}
