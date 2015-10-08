<?php
namespace Recoil\Amqp\Protocol;

trait MethodSerializerTrait
{
    public function visitConnectionStartOkFrame(Connection\StartOkFrame $frame)
    {
        $payload = "\x00\x0a\x00\x0b";

        // serialize "client-properties" (table)
        $payload .= $this->serializeTable($frame->clientProperties);

        // serialize "mechanism" (shortstr)
        $payload .= $this->serializeShortString($frame->mechanism);

        // serialize "response" (longstr)
        $payload .= $this->serializeLongString($frame->response);

        // serialize "locale" (shortstr)
        $payload .= $this->serializeShortString($frame->locale);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitConnectionSecureOkFrame(Connection\SecureOkFrame $frame)
    {
        $payload = "\x00\x0a\x00\x15";

        // serialize "response" (longstr)
        $payload .= $this->serializeLongString($frame->response);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitConnectionTuneOkFrame(Connection\TuneOkFrame $frame)
    {
        $payload = "\x00\x0a\x00\x1f";

        // serialize "channel-max" (short)
        // serialize "frame-max" (long)
        // serialize "heartbeat" (short)
        $payload .= pack('nNn', $frame->channelMax, $frame->frameMax, $frame->heartbeat);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitConnectionOpenFrame(Connection\OpenFrame $frame)
    {
        $payload = "\x00\x0a\x00\x28";

        // serialize "virtual-host" (shortstr)
        $payload .= $this->serializeShortString($frame->virtualHost);

        // serialize "capabilities" (shortstr)
        $payload .= $this->serializeShortString($frame->capabilities);

        // serialize "insist" (bit)
        $payload .= pack("C", $frame->insist ? 1 : 0);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitConnectionCloseFrame(Connection\CloseFrame $frame)
    {
        $payload = "\x00\x0a\x00\x32";

        // serialize "replyCode" (short)
        $payload .= pack('n', $frame->replyCode);

        // serialize "reply-text" (shortstr)
        $payload .= $this->serializeShortString($frame->replyText);

        // serialize "class-id" (short)
        // serialize "method-id" (short)
        $payload .= pack('nn', $frame->classId, $frame->methodId);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitConnectionCloseOkFrame(Connection\CloseOkFrame $frame)
    {
        $payload = "\x00\x0a\x00\x33";

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitChannelOpenFrame(Channel\OpenFrame $frame)
    {
        $payload = "\x00\x14\x00\x0a";

        // serialize "out-of-band" (shortstr)
        $payload .= $this->serializeShortString($frame->outOfBand);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitChannelFlowFrame(Channel\FlowFrame $frame)
    {
        $payload = "\x00\x14\x00\x14";

        // serialize "active" (bit)
        $payload .= pack("C", $frame->active ? 1 : 0);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitChannelFlowOkFrame(Channel\FlowOkFrame $frame)
    {
        $payload = "\x00\x14\x00\x15";

        // serialize "active" (bit)
        $payload .= pack("C", $frame->active ? 1 : 0);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitChannelCloseFrame(Channel\CloseFrame $frame)
    {
        $payload = "\x00\x14\x00\x28";

        // serialize "replyCode" (short)
        $payload .= pack('n', $frame->replyCode);

        // serialize "reply-text" (shortstr)
        $payload .= $this->serializeShortString($frame->replyText);

        // serialize "class-id" (short)
        // serialize "method-id" (short)
        $payload .= pack('nn', $frame->classId, $frame->methodId);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitChannelCloseOkFrame(Channel\CloseOkFrame $frame)
    {
        $payload = "\x00\x14\x00\x29";

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitAccessRequestFrame(Access\RequestFrame $frame)
    {
        $payload = "\x00\x1e\x00\x0a";

        // serialize "realm" (shortstr)
        $payload .= $this->serializeShortString($frame->realm);

        // serialize "exclusive" (bit)
        // serialize "passive" (bit)
        // serialize "active" (bit)
        // serialize "write" (bit)
        // serialize "read" (bit)
        $payload .= ord(
               (int) $frame->exclusive
            | ((int) $frame->passive << 1)
            | ((int) $frame->active << 2)
            | ((int) $frame->write << 3)
            | ((int) $frame->read << 4)
        );

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitExchangeDeclareFrame(Exchange\DeclareFrame $frame)
    {
        $payload = "\x00\x28\x00\x0a";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "exchange" (shortstr)
        $payload .= $this->serializeShortString($frame->exchange);

        // serialize "type" (shortstr)
        $payload .= $this->serializeShortString($frame->type);

        // serialize "passive" (bit)
        // serialize "durable" (bit)
        // serialize "auto-delete" (bit)
        // serialize "internal" (bit)
        // serialize "nowait" (bit)
        $payload .= ord(
               (int) $frame->passive
            | ((int) $frame->durable << 1)
            | ((int) $frame->autoDelete << 2)
            | ((int) $frame->internal << 3)
            | ((int) $frame->nowait << 4)
        );

        // serialize "arguments" (table)
        $payload .= $this->serializeTable($frame->arguments);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitExchangeDeleteFrame(Exchange\DeleteFrame $frame)
    {
        $payload = "\x00\x28\x00\x14";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "exchange" (shortstr)
        $payload .= $this->serializeShortString($frame->exchange);

        // serialize "if-unused" (bit)
        // serialize "nowait" (bit)
        $payload .= ord(
               (int) $frame->ifUnused
            | ((int) $frame->nowait << 1)
        );

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitExchangeBindFrame(Exchange\BindFrame $frame)
    {
        $payload = "\x00\x28\x00\x1e";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "destination" (shortstr)
        $payload .= $this->serializeShortString($frame->destination);

        // serialize "source" (shortstr)
        $payload .= $this->serializeShortString($frame->source);

        // serialize "routing-key" (shortstr)
        $payload .= $this->serializeShortString($frame->routingKey);

        // serialize "nowait" (bit)
        $payload .= pack("C", $frame->nowait ? 1 : 0);

        // serialize "arguments" (table)
        $payload .= $this->serializeTable($frame->arguments);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitExchangeUnbindFrame(Exchange\UnbindFrame $frame)
    {
        $payload = "\x00\x28\x00\x28";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "destination" (shortstr)
        $payload .= $this->serializeShortString($frame->destination);

        // serialize "source" (shortstr)
        $payload .= $this->serializeShortString($frame->source);

        // serialize "routing-key" (shortstr)
        $payload .= $this->serializeShortString($frame->routingKey);

        // serialize "nowait" (bit)
        $payload .= pack("C", $frame->nowait ? 1 : 0);

        // serialize "arguments" (table)
        $payload .= $this->serializeTable($frame->arguments);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitQueueDeclareFrame(Queue\DeclareFrame $frame)
    {
        $payload = "\x00\x32\x00\x0a";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "queue" (shortstr)
        $payload .= $this->serializeShortString($frame->queue);

        // serialize "passive" (bit)
        // serialize "durable" (bit)
        // serialize "exclusive" (bit)
        // serialize "auto-delete" (bit)
        // serialize "nowait" (bit)
        $payload .= ord(
               (int) $frame->passive
            | ((int) $frame->durable << 1)
            | ((int) $frame->exclusive << 2)
            | ((int) $frame->autoDelete << 3)
            | ((int) $frame->nowait << 4)
        );

        // serialize "arguments" (table)
        $payload .= $this->serializeTable($frame->arguments);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitQueueBindFrame(Queue\BindFrame $frame)
    {
        $payload = "\x00\x32\x00\x14";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "queue" (shortstr)
        $payload .= $this->serializeShortString($frame->queue);

        // serialize "exchange" (shortstr)
        $payload .= $this->serializeShortString($frame->exchange);

        // serialize "routing-key" (shortstr)
        $payload .= $this->serializeShortString($frame->routingKey);

        // serialize "nowait" (bit)
        $payload .= pack("C", $frame->nowait ? 1 : 0);

        // serialize "arguments" (table)
        $payload .= $this->serializeTable($frame->arguments);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitQueuePurgeFrame(Queue\PurgeFrame $frame)
    {
        $payload = "\x00\x32\x00\x1e";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "queue" (shortstr)
        $payload .= $this->serializeShortString($frame->queue);

        // serialize "nowait" (bit)
        $payload .= pack("C", $frame->nowait ? 1 : 0);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitQueueDeleteFrame(Queue\DeleteFrame $frame)
    {
        $payload = "\x00\x32\x00\x28";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "queue" (shortstr)
        $payload .= $this->serializeShortString($frame->queue);

        // serialize "if-unused" (bit)
        // serialize "if-empty" (bit)
        // serialize "nowait" (bit)
        $payload .= ord(
               (int) $frame->ifUnused
            | ((int) $frame->ifEmpty << 1)
            | ((int) $frame->nowait << 2)
        );

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitQueueUnbindFrame(Queue\UnbindFrame $frame)
    {
        $payload = "\x00\x32\x00\x32";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "queue" (shortstr)
        $payload .= $this->serializeShortString($frame->queue);

        // serialize "exchange" (shortstr)
        $payload .= $this->serializeShortString($frame->exchange);

        // serialize "routing-key" (shortstr)
        $payload .= $this->serializeShortString($frame->routingKey);

        // serialize "arguments" (table)
        $payload .= $this->serializeTable($frame->arguments);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitBasicQosFrame(Basic\QosFrame $frame)
    {
        $payload = "\x00\x3c\x00\x0a";

        // serialize "global" (bit)
        $payload .= pack("C", $frame->global ? 1 : 0);

        // serialize "prefetch-size" (long)
        // serialize "prefetch-count" (short)
        $payload .= pack('Nn', $frame->prefetchSize, $frame->prefetchCount);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitBasicConsumeFrame(Basic\ConsumeFrame $frame)
    {
        $payload = "\x00\x3c\x00\x14";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "queue" (shortstr)
        $payload .= $this->serializeShortString($frame->queue);

        // serialize "consumer-tag" (shortstr)
        $payload .= $this->serializeShortString($frame->consumerTag);

        // serialize "no-local" (bit)
        // serialize "no-ack" (bit)
        // serialize "exclusive" (bit)
        // serialize "nowait" (bit)
        $payload .= ord(
               (int) $frame->noLocal
            | ((int) $frame->noAck << 1)
            | ((int) $frame->exclusive << 2)
            | ((int) $frame->nowait << 3)
        );

        // serialize "arguments" (table)
        $payload .= $this->serializeTable($frame->arguments);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitBasicCancelFrame(Basic\CancelFrame $frame)
    {
        $payload = "\x00\x3c\x00\x1e";

        // serialize "consumer-tag" (shortstr)
        $payload .= $this->serializeShortString($frame->consumerTag);

        // serialize "nowait" (bit)
        $payload .= pack("C", $frame->nowait ? 1 : 0);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitBasicPublishFrame(Basic\PublishFrame $frame)
    {
        $payload = "\x00\x3c\x00\x28";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "exchange" (shortstr)
        $payload .= $this->serializeShortString($frame->exchange);

        // serialize "routing-key" (shortstr)
        $payload .= $this->serializeShortString($frame->routingKey);

        // serialize "mandatory" (bit)
        // serialize "immediate" (bit)
        $payload .= ord(
               (int) $frame->mandatory
            | ((int) $frame->immediate << 1)
        );

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitBasicGetFrame(Basic\GetFrame $frame)
    {
        $payload = "\x00\x3c\x00\x46";

        // serialize "reserved" (short)
        $payload .= pack('n', $frame->reserved);

        // serialize "queue" (shortstr)
        $payload .= $this->serializeShortString($frame->queue);

        // serialize "no-ack" (bit)
        $payload .= pack("C", $frame->noAck ? 1 : 0);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitBasicAckFrame(Basic\AckFrame $frame)
    {
        $payload = "\x00\x3c\x00\x50";

        // serialize "multiple" (bit)
        $payload .= pack("C", $frame->multiple ? 1 : 0);

        // serialize "deliveryTag" (longlong)
        $payload .= pack('J', $frame->deliveryTag);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitBasicRejectFrame(Basic\RejectFrame $frame)
    {
        $payload = "\x00\x3c\x00\x5a";

        // serialize "requeue" (bit)
        $payload .= pack("C", $frame->requeue ? 1 : 0);

        // serialize "deliveryTag" (longlong)
        $payload .= pack('J', $frame->deliveryTag);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitBasicRecoverAsyncFrame(Basic\RecoverAsyncFrame $frame)
    {
        $payload = "\x00\x3c\x00\x64";

        // serialize "requeue" (bit)
        $payload .= pack("C", $frame->requeue ? 1 : 0);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitBasicRecoverFrame(Basic\RecoverFrame $frame)
    {
        $payload = "\x00\x3c\x00\x6e";

        // serialize "requeue" (bit)
        $payload .= pack("C", $frame->requeue ? 1 : 0);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitBasicNackFrame(Basic\NackFrame $frame)
    {
        $payload = "\x00\x3c\x00\x78";

        // serialize "multiple" (bit)
        // serialize "requeue" (bit)
        $payload .= ord(
               (int) $frame->multiple
            | ((int) $frame->requeue << 1)
        );

        // serialize "deliveryTag" (longlong)
        $payload .= pack('J', $frame->deliveryTag);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitTxSelectFrame(Tx\SelectFrame $frame)
    {
        $payload = "\x00\x5a\x00\x0a";

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitTxCommitFrame(Tx\CommitFrame $frame)
    {
        $payload = "\x00\x5a\x00\x14";

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitTxRollbackFrame(Tx\RollbackFrame $frame)
    {
        $payload = "\x00\x5a\x00\x1e";

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

    public function visitConfirmSelectFrame(Confirm\SelectFrame $frame)
    {
        $payload = "\x00\x55\x00\x0a";

        // serialize "nowait" (bit)
        $payload .= pack("C", $frame->nowait ? 1 : 0);

        return pack("CnN", AmqpConstants::FRAME_METHOD, $frame->channel, strlen($payload))
             . $payload
             . chr(AmqpConstants::FRAME_END);
    }

}
