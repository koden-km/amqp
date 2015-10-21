<?php

namespace Recoil\Amqp\v091\Protocol;

// S = machine order unsigned short, v = little-endian order
if (pack('S', 1) === pack('v', 1)) {
    final class Endianness
    {
        const LITTLE = true;
        const BIG = false;
    }
} else {
    final class Endianness
    {
        const LITTLE = false;
        const BIG = true;
    }
}
