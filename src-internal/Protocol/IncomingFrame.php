<?php
namespace Recoil\Amqp\Protocol;

interface IncomingFrame
{
    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor);
}
