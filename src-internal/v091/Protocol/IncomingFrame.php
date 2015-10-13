<?php

namespace Recoil\Amqp\v091\Protocol;

interface IncomingFrame
{
    public function acceptIncoming(IncomingFrameVisitor $visitor);
}
