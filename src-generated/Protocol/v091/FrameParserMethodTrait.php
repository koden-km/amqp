<?php
namespace Recoil\Amqp\Protocol\v091;

trait FrameParserMethodTrait
{
    private function parseMethodFrame()
    {
        list(, $class, $method) = unpack("n2", $this->buffer);
        $this->buffer = substr($this->buffer, 4);

        // class "connection"
        if ($class === 10) {

            // method "connection.start"
            if ($method === 10) {
                $frame = new Connection\StartFrame();

                // consume (a) "version-major" (octet)
                // consume (b) "version-minor" (octet)
                $fields = unpack('ca/cb', $this->buffer);
                $this->buffer = substr($this->buffer, 2);
                $frame->versionMajor = $fields["a"];
                $frame->versionMinor = $fields["b"];

                // consume "server-properties" (table)
                $frame->serverProperties = $this->parseFieldTable();

                // consume "mechanisms" (longstr)
                $frame->mechanisms = $this->parseLongString();

                // consume "locales" (longstr)
                $frame->locales = $this->parseLongString();

                return $frame;

            // method "connection.secure"
            } elseif ($method === 20) {
                $frame = new Connection\SecureFrame();

                // consume "challenge" (longstr)
                $frame->challenge = $this->parseLongString();

                return $frame;

            // method "connection.tune"
            } elseif ($method === 30) {
                $frame = new Connection\TuneFrame();

                // consume (a) "channel-max" (short)
                // consume (b) "frame-max" (long)
                // consume (c) "heartbeat" (short)
                $fields = unpack('na/Nb/nc', $this->buffer);
                $this->buffer = substr($this->buffer, 8);
                $frame->channelMax = $fields["a"];
                $frame->frameMax = $fields["b"];
                $frame->heartbeat = $fields["c"];

                return $frame;

            // method "connection.open-ok"
            } elseif ($method === 41) {
                $frame = new Connection\OpenOkFrame();

                // consume "known-hosts" (shortstr)
                $frame->knownHosts = $this->parseShortString();

                return $frame;

            // method "connection.close"
            } elseif ($method === 50) {
                $frame = new Connection\CloseFrame();

                // consume "replyCode" (short)
                list(, $frame->replyCode) = unpack('n', $this->buffer);
                $this->buffer = substr($this->buffer, 2);

                // consume "reply-text" (shortstr)
                $frame->replyText = $this->parseShortString();

                // consume (a) "class-id" (short)
                // consume (b) "method-id" (short)
                $fields = unpack('na/nb', $this->buffer);
                $this->buffer = substr($this->buffer, 4);
                $frame->classId = $fields["a"];
                $frame->methodId = $fields["b"];

                return $frame;

            // method "connection.close-ok"
            } elseif ($method === 51) {
                return new Connection\CloseOkFrame();

            // method "connection.blocked"
            } elseif ($method === 60) {
                $frame = new Connection\BlockedFrame();

                // consume "reason" (shortstr)
                $frame->reason = $this->parseShortString();

                return $frame;

            // method "connection.unblocked"
            } elseif ($method === 61) {
                return new Connection\UnblockedFrame();
            }

            throw ProtocolException::create(
                "Frame method (" . $method . ") is invalid for class \"connection\"."
            );

        // class "channel"
        } elseif ($class === 20) {

            // method "channel.open-ok"
            if ($method === 11) {
                $frame = new Channel\OpenOkFrame();

                // consume "channel-id" (longstr)
                $frame->channelId = $this->parseLongString();

                return $frame;

            // method "channel.flow"
            } elseif ($method === 20) {
                $frame = new Channel\FlowFrame();

                // consume "active" (bit)
                $frame->active = ord($this->buffer) !== 0;
                $this->buffer = substr($this->buffer, 1);

                return $frame;

            // method "channel.flow-ok"
            } elseif ($method === 21) {
                $frame = new Channel\FlowOkFrame();

                // consume "active" (bit)
                $frame->active = ord($this->buffer) !== 0;
                $this->buffer = substr($this->buffer, 1);

                return $frame;

            // method "channel.close"
            } elseif ($method === 40) {
                $frame = new Channel\CloseFrame();

                // consume "replyCode" (short)
                list(, $frame->replyCode) = unpack('n', $this->buffer);
                $this->buffer = substr($this->buffer, 2);

                // consume "reply-text" (shortstr)
                $frame->replyText = $this->parseShortString();

                // consume (a) "class-id" (short)
                // consume (b) "method-id" (short)
                $fields = unpack('na/nb', $this->buffer);
                $this->buffer = substr($this->buffer, 4);
                $frame->classId = $fields["a"];
                $frame->methodId = $fields["b"];

                return $frame;

            // method "channel.close-ok"
            } elseif ($method === 41) {
                return new Channel\CloseOkFrame();
            }

            throw ProtocolException::create(
                "Frame method (" . $method . ") is invalid for class \"channel\"."
            );

        // class "access"
        } elseif ($class === 30) {

            // method "access.request-ok"
            if ($method === 11) {
                $frame = new Access\RequestOkFrame();

                // consume "reserved1" (short)
                list(, $frame->reserved1) = unpack('n', $this->buffer);
                $this->buffer = substr($this->buffer, 2);

                return $frame;
            }

            throw ProtocolException::create(
                "Frame method (" . $method . ") is invalid for class \"access\"."
            );

        // class "exchange"
        } elseif ($class === 40) {

            // method "exchange.declare-ok"
            if ($method === 11) {
                return new Exchange\DeclareOkFrame();

            // method "exchange.delete-ok"
            } elseif ($method === 21) {
                return new Exchange\DeleteOkFrame();

            // method "exchange.bind-ok"
            } elseif ($method === 31) {
                return new Exchange\BindOkFrame();

            // method "exchange.unbind-ok"
            } elseif ($method === 51) {
                return new Exchange\UnbindOkFrame();
            }

            throw ProtocolException::create(
                "Frame method (" . $method . ") is invalid for class \"exchange\"."
            );

        // class "queue"
        } elseif ($class === 50) {

            // method "queue.declare-ok"
            if ($method === 11) {
                $frame = new Queue\DeclareOkFrame();

                // consume "queue" (shortstr)
                $frame->queue = $this->parseShortString();

                // consume (a) "message-count" (long)
                // consume (b) "consumer-count" (long)
                $fields = unpack('Na/Nb', $this->buffer);
                $this->buffer = substr($this->buffer, 8);
                $frame->messageCount = $fields["a"];
                $frame->consumerCount = $fields["b"];

                return $frame;

            // method "queue.bind-ok"
            } elseif ($method === 21) {
                return new Queue\BindOkFrame();

            // method "queue.purge-ok"
            } elseif ($method === 31) {
                $frame = new Queue\PurgeOkFrame();

                // consume "messageCount" (long)
                list(, $frame->messageCount) = unpack('N', $this->buffer);
                $this->buffer = substr($this->buffer, 4);

                return $frame;

            // method "queue.delete-ok"
            } elseif ($method === 41) {
                $frame = new Queue\DeleteOkFrame();

                // consume "messageCount" (long)
                list(, $frame->messageCount) = unpack('N', $this->buffer);
                $this->buffer = substr($this->buffer, 4);

                return $frame;

            // method "queue.unbind-ok"
            } elseif ($method === 51) {
                return new Queue\UnbindOkFrame();
            }

            throw ProtocolException::create(
                "Frame method (" . $method . ") is invalid for class \"queue\"."
            );

        // class "basic"
        } elseif ($class === 60) {

            // method "basic.qos-ok"
            if ($method === 11) {
                return new Basic\QosOkFrame();

            // method "basic.consume-ok"
            } elseif ($method === 21) {
                $frame = new Basic\ConsumeOkFrame();

                // consume "consumer-tag" (shortstr)
                $frame->consumerTag = $this->parseShortString();

                return $frame;

            // method "basic.cancel-ok"
            } elseif ($method === 31) {
                $frame = new Basic\CancelOkFrame();

                // consume "consumer-tag" (shortstr)
                $frame->consumerTag = $this->parseShortString();

                return $frame;

            // method "basic.return"
            } elseif ($method === 50) {
                $frame = new Basic\ReturnFrame();

                // consume "replyCode" (short)
                list(, $frame->replyCode) = unpack('n', $this->buffer);
                $this->buffer = substr($this->buffer, 2);

                // consume "reply-text" (shortstr)
                $frame->replyText = $this->parseShortString();

                // consume "exchange" (shortstr)
                $frame->exchange = $this->parseShortString();

                // consume "routing-key" (shortstr)
                $frame->routingKey = $this->parseShortString();

                return $frame;

            // method "basic.deliver"
            } elseif ($method === 60) {
                $frame = new Basic\DeliverFrame();

                // consume "consumer-tag" (shortstr)
                $frame->consumerTag = $this->parseShortString();

                // consume "redelivered" (bit)
                $frame->redelivered = ord($this->buffer) !== 0;
                $this->buffer = substr($this->buffer, 1);

                // consume "deliveryTag" (longlong)
                list(, $frame->deliveryTag) = unpack('J', $this->buffer);
                $this->buffer = substr($this->buffer, 8);

                // consume "exchange" (shortstr)
                $frame->exchange = $this->parseShortString();

                // consume "routing-key" (shortstr)
                $frame->routingKey = $this->parseShortString();

                return $frame;

            // method "basic.get-ok"
            } elseif ($method === 71) {
                $frame = new Basic\GetOkFrame();

                // consume "redelivered" (bit)
                $frame->redelivered = ord($this->buffer) !== 0;
                $this->buffer = substr($this->buffer, 1);

                // consume "deliveryTag" (longlong)
                list(, $frame->deliveryTag) = unpack('J', $this->buffer);
                $this->buffer = substr($this->buffer, 8);

                // consume "exchange" (shortstr)
                $frame->exchange = $this->parseShortString();

                // consume "routing-key" (shortstr)
                $frame->routingKey = $this->parseShortString();

                // consume "messageCount" (long)
                list(, $frame->messageCount) = unpack('N', $this->buffer);
                $this->buffer = substr($this->buffer, 4);

                return $frame;

            // method "basic.get-empty"
            } elseif ($method === 72) {
                $frame = new Basic\GetEmptyFrame();

                // consume "cluster-id" (shortstr)
                $frame->clusterId = $this->parseShortString();

                return $frame;

            // method "basic.ack"
            } elseif ($method === 80) {
                $frame = new Basic\AckFrame();

                // consume "multiple" (bit)
                $frame->multiple = ord($this->buffer) !== 0;
                $this->buffer = substr($this->buffer, 1);

                // consume "deliveryTag" (longlong)
                list(, $frame->deliveryTag) = unpack('J', $this->buffer);
                $this->buffer = substr($this->buffer, 8);

                return $frame;

            // method "basic.recover-ok"
            } elseif ($method === 111) {
                return new Basic\RecoverOkFrame();

            // method "basic.nack"
            } elseif ($method === 120) {
                $frame = new Basic\NackFrame();

                // consume "multiple" (bit)
                // consume "requeue" (bit)
                $octet = ord($this->buffer);
                $this->buffer = substr($this->buffer, 1);
                $frame->multiple = $octet & 1 !== 0;
                $frame->requeue = $octet & 2 !== 0;

                // consume "deliveryTag" (longlong)
                list(, $frame->deliveryTag) = unpack('J', $this->buffer);
                $this->buffer = substr($this->buffer, 8);

                return $frame;
            }

            throw ProtocolException::create(
                "Frame method (" . $method . ") is invalid for class \"basic\"."
            );

        // class "tx"
        } elseif ($class === 90) {

            // method "tx.select-ok"
            if ($method === 11) {
                return new Tx\SelectOkFrame();

            // method "tx.commit-ok"
            } elseif ($method === 21) {
                return new Tx\CommitOkFrame();

            // method "tx.rollback-ok"
            } elseif ($method === 31) {
                return new Tx\RollbackOkFrame();
            }

            throw ProtocolException::create(
                "Frame method (" . $method . ") is invalid for class \"tx\"."
            );

        // class "confirm"
        } elseif ($class === 85) {

            // method "confirm.select-ok"
            if ($method === 11) {
                return new Confirm\SelectOkFrame();
            }

            throw ProtocolException::create(
                "Frame method (" . $method . ") is invalid for class \"confirm\"."
            );
        }

        throw ProtocolException::create("Frame class (" . $class . ") is invalid.");
    }
}
