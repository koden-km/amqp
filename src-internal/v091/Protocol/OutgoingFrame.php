<?php
namespace Recoil\Amqp\v091\Protocol;

interface OutgoingFrame
{
    public function acceptOutgoing(OutgoingFrameVisitor $visitor);
}
