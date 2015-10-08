<?php
namespace Recoil\Amqp\Protocol;

interface OutgoingFrame
{
    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor);
}
