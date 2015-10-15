<?php
namespace Recoil\Amqp\v091\Protocol\Access;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class AccessRequestFrame implements OutgoingFrame
{
    public $channel;
    public $realm; // shortstr
    public $exclusive; // bit
    public $passive; // bit
    public $active; // bit
    public $write; // bit
    public $read; // bit

    public static function create(
        $channel = 0
      , $realm = null
      , $exclusive = null
      , $passive = null
      , $active = null
      , $write = null
      , $read = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->realm = null === $realm ? '/data' : $realm;
        $frame->exclusive = null === $exclusive ? false : $exclusive;
        $frame->passive = null === $passive ? true : $passive;
        $frame->active = null === $active ? true : $active;
        $frame->write = null === $write ? true : $write;
        $frame->read = null === $read ? true : $read;

        return $frame;
    }
}
