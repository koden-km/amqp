<?php
namespace Recoil\Amqp\Protocol;

interface Frame
{
    public function accept(FrameVisitor $visitor);
}
