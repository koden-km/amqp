<?php
namespace Recoil\Amqp\Protocol\v091;

trait MethodSerializerTrait
{
    public function visitConnectionStartOkFrame(Connection\StartOkFrame $frame)
    {
        $payload = "\x00\x0a\x00\x0b"
                 . $this->serializeTable($frame->clientProperties)
                 . $this->serializeShortString($frame->mechanism)
                 . $this->serializeLongString($frame->response)
                 . $this->serializeShortString($frame->locale)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitConnectionSecureOkFrame(Connection\SecureOkFrame $frame)
    {
        $payload = "\x00\x0a\x00\x15"
                 . $this->serializeLongString($frame->response)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitConnectionTuneOkFrame(Connection\TuneOkFrame $frame)
    {
        $payload = "\x00\x0a\x00\x1f"
                 . pack('nNn', $frame->channelMax, $frame->frameMax, $frame->heartbeat)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitConnectionOpenFrame(Connection\OpenFrame $frame)
    {
        $payload = "\x00\x0a\x00\x28"
                 . $this->serializeShortString($frame->virtualHost)
                 . $this->serializeShortString($frame->capabilities)
                 . ($frame->insist ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitConnectionCloseFrame(Connection\CloseFrame $frame)
    {
        $payload = "\x00\x0a\x00\x32"
                 . pack('n', $frame->replyCode)
                 . $this->serializeShortString($frame->replyText)
                 . pack('nn', $frame->classId, $frame->methodId)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitConnectionCloseOkFrame(Connection\CloseOkFrame $frame)
    {
        return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x0a\x00\x33\xce";
    }

    public function visitChannelOpenFrame(Channel\OpenFrame $frame)
    {
        $payload = "\x00\x14\x00\x0a"
                 . $this->serializeShortString($frame->outOfBand)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitChannelFlowFrame(Channel\FlowFrame $frame)
    {
        $payload = "\x00\x14\x00\x14"
                 . ($frame->active ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitChannelFlowOkFrame(Channel\FlowOkFrame $frame)
    {
        $payload = "\x00\x14\x00\x15"
                 . ($frame->active ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitChannelCloseFrame(Channel\CloseFrame $frame)
    {
        $payload = "\x00\x14\x00\x28"
                 . pack('n', $frame->replyCode)
                 . $this->serializeShortString($frame->replyText)
                 . pack('nn', $frame->classId, $frame->methodId)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitChannelCloseOkFrame(Channel\CloseOkFrame $frame)
    {
        return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x14\x00\x29\xce";
    }

    public function visitAccessRequestFrame(Access\RequestFrame $frame)
    {
        $payload = "\x00\x1e\x00\x0a"
                 . $this->serializeShortString($frame->realm)
                 . chr(
                       $frame->exclusive
                     | $frame->passive << 1
                     | $frame->active << 2
                     | $frame->write << 3
                     | $frame->read << 4
                 )
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitExchangeDeclareFrame(Exchange\DeclareFrame $frame)
    {
        $payload = "\x00\x28\x00\x0a"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->exchange)
                 . $this->serializeShortString($frame->type)
                 . chr(
                       $frame->passive
                     | $frame->durable << 1
                     | $frame->autoDelete << 2
                     | $frame->internal << 3
                     | $frame->nowait << 4
                 )
                 . $this->serializeTable($frame->arguments)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitExchangeDeleteFrame(Exchange\DeleteFrame $frame)
    {
        $payload = "\x00\x28\x00\x14"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->exchange)
                 . chr(
                       $frame->ifUnused
                     | $frame->nowait << 1
                 )
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitExchangeBindFrame(Exchange\BindFrame $frame)
    {
        $payload = "\x00\x28\x00\x1e"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->destination)
                 . $this->serializeShortString($frame->source)
                 . $this->serializeShortString($frame->routingKey)
                 . ($frame->nowait ? "\x01" : "\x00")
                 . $this->serializeTable($frame->arguments)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitExchangeUnbindFrame(Exchange\UnbindFrame $frame)
    {
        $payload = "\x00\x28\x00\x28"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->destination)
                 . $this->serializeShortString($frame->source)
                 . $this->serializeShortString($frame->routingKey)
                 . ($frame->nowait ? "\x01" : "\x00")
                 . $this->serializeTable($frame->arguments)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitQueueDeclareFrame(Queue\DeclareFrame $frame)
    {
        $payload = "\x00\x32\x00\x0a"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->queue)
                 . chr(
                       $frame->passive
                     | $frame->durable << 1
                     | $frame->exclusive << 2
                     | $frame->autoDelete << 3
                     | $frame->nowait << 4
                 )
                 . $this->serializeTable($frame->arguments)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitQueueBindFrame(Queue\BindFrame $frame)
    {
        $payload = "\x00\x32\x00\x14"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->queue)
                 . $this->serializeShortString($frame->exchange)
                 . $this->serializeShortString($frame->routingKey)
                 . ($frame->nowait ? "\x01" : "\x00")
                 . $this->serializeTable($frame->arguments)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitQueuePurgeFrame(Queue\PurgeFrame $frame)
    {
        $payload = "\x00\x32\x00\x1e"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->queue)
                 . ($frame->nowait ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitQueueDeleteFrame(Queue\DeleteFrame $frame)
    {
        $payload = "\x00\x32\x00\x28"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->queue)
                 . chr(
                       $frame->ifUnused
                     | $frame->ifEmpty << 1
                     | $frame->nowait << 2
                 )
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitQueueUnbindFrame(Queue\UnbindFrame $frame)
    {
        $payload = "\x00\x32\x00\x32"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->queue)
                 . $this->serializeShortString($frame->exchange)
                 . $this->serializeShortString($frame->routingKey)
                 . $this->serializeTable($frame->arguments)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitBasicQosFrame(Basic\QosFrame $frame)
    {
        $payload = "\x00\x3c\x00\x0a"
                 . ($frame->global ? "\x01" : "\x00")
                 . pack('Nn', $frame->prefetchSize, $frame->prefetchCount)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitBasicConsumeFrame(Basic\ConsumeFrame $frame)
    {
        $payload = "\x00\x3c\x00\x14"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->queue)
                 . $this->serializeShortString($frame->consumerTag)
                 . chr(
                       $frame->noLocal
                     | $frame->noAck << 1
                     | $frame->exclusive << 2
                     | $frame->nowait << 3
                 )
                 . $this->serializeTable($frame->arguments)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitBasicCancelFrame(Basic\CancelFrame $frame)
    {
        $payload = "\x00\x3c\x00\x1e"
                 . $this->serializeShortString($frame->consumerTag)
                 . ($frame->nowait ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitBasicPublishFrame(Basic\PublishFrame $frame)
    {
        $payload = "\x00\x3c\x00\x28"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->exchange)
                 . $this->serializeShortString($frame->routingKey)
                 . chr(
                       $frame->mandatory
                     | $frame->immediate << 1
                 )
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitBasicGetFrame(Basic\GetFrame $frame)
    {
        $payload = "\x00\x3c\x00\x46"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->queue)
                 . ($frame->noAck ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitBasicAckFrame(Basic\AckFrame $frame)
    {
        $payload = "\x00\x3c\x00\x50"
                 . ($frame->multiple ? "\x01" : "\x00")
                 . pack('J', $frame->deliveryTag)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitBasicRejectFrame(Basic\RejectFrame $frame)
    {
        $payload = "\x00\x3c\x00\x5a"
                 . ($frame->requeue ? "\x01" : "\x00")
                 . pack('J', $frame->deliveryTag)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitBasicRecoverAsyncFrame(Basic\RecoverAsyncFrame $frame)
    {
        $payload = "\x00\x3c\x00\x64"
                 . ($frame->requeue ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitBasicRecoverFrame(Basic\RecoverFrame $frame)
    {
        $payload = "\x00\x3c\x00\x6e"
                 . ($frame->requeue ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitBasicNackFrame(Basic\NackFrame $frame)
    {
        $payload = "\x00\x3c\x00\x78"
                 . chr(
                       $frame->multiple
                     | $frame->requeue << 1
                 )
                 . pack('J', $frame->deliveryTag)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitTxSelectFrame(Tx\SelectFrame $frame)
    {
        return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x5a\x00\x0a\xce";
    }

    public function visitTxCommitFrame(Tx\CommitFrame $frame)
    {
        return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x5a\x00\x14\xce";
    }

    public function visitTxRollbackFrame(Tx\RollbackFrame $frame)
    {
        return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x5a\x00\x1e\xce";
    }

    public function visitConfirmSelectFrame(Confirm\SelectFrame $frame)
    {
        $payload = "\x00\x55\x00\x0a"
                 . ($frame->nowait ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

}
