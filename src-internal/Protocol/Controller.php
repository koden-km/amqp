<?php
namespace Recoil\Amqp\Protocol;

interface Controller
{
    public function accept(FrameVisitor $visitor);
}
