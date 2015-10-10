<?php
namespace Recoil\Amqp\v091\Protocol;

trait MethodSerializerTrait
{
    public function visitOutgoingConnectionStartOkFrame(Connection\ConnectionStartOkFrame $frame)
    {
        $payload = "\x00\x0a\x00\x0b"
                 . $this->serializeTable($frame->clientProperties)
                 . $this->serializeShortString($frame->mechanism)
                 . $this->serializeLongString($frame->response)
                 . $this->serializeShortString($frame->locale)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingConnectionSecureOkFrame(Connection\ConnectionSecureOkFrame $frame)
    {
        $payload = "\x00\x0a\x00\x15"
                 . $this->serializeLongString($frame->response)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingConnectionTuneOkFrame(Connection\ConnectionTuneOkFrame $frame)
    {
        $payload = "\x00\x0a\x00\x1f"
                 . pack('nNn', $frame->channelMax, $frame->frameMax, $frame->heartbeat)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingConnectionOpenFrame(Connection\ConnectionOpenFrame $frame)
    {
        $payload = "\x00\x0a\x00\x28"
                 . $this->serializeShortString($frame->virtualHost)
                 . $this->serializeShortString($frame->capabilities)
                 . ($frame->insist ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingConnectionCloseFrame(Connection\ConnectionCloseFrame $frame)
    {
        $payload = "\x00\x0a\x00\x32"
                 . pack('n', $frame->replyCode)
                 . $this->serializeShortString($frame->replyText)
                 . pack('nn', $frame->classId, $frame->methodId)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingConnectionCloseOkFrame(Connection\ConnectionCloseOkFrame $frame)
    {
        return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x0a\x00\x33\xce";
    }

    public function visitOutgoingChannelOpenFrame(Channel\ChannelOpenFrame $frame)
    {
        $payload = "\x00\x14\x00\x0a"
                 . $this->serializeShortString($frame->outOfBand)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingChannelFlowFrame(Channel\ChannelFlowFrame $frame)
    {
        $payload = "\x00\x14\x00\x14"
                 . ($frame->active ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingChannelFlowOkFrame(Channel\ChannelFlowOkFrame $frame)
    {
        $payload = "\x00\x14\x00\x15"
                 . ($frame->active ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingChannelCloseFrame(Channel\ChannelCloseFrame $frame)
    {
        $payload = "\x00\x14\x00\x28"
                 . pack('n', $frame->replyCode)
                 . $this->serializeShortString($frame->replyText)
                 . pack('nn', $frame->classId, $frame->methodId)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingChannelCloseOkFrame(Channel\ChannelCloseOkFrame $frame)
    {
        return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x14\x00\x29\xce";
    }

    public function visitOutgoingAccessRequestFrame(Access\AccessRequestFrame $frame)
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

    public function visitOutgoingExchangeDeclareFrame(Exchange\ExchangeDeclareFrame $frame)
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

    public function visitOutgoingExchangeDeleteFrame(Exchange\ExchangeDeleteFrame $frame)
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

    public function visitOutgoingExchangeBindFrame(Exchange\ExchangeBindFrame $frame)
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

    public function visitOutgoingExchangeUnbindFrame(Exchange\ExchangeUnbindFrame $frame)
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

    public function visitOutgoingQueueDeclareFrame(Queue\QueueDeclareFrame $frame)
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

    public function visitOutgoingQueueBindFrame(Queue\QueueBindFrame $frame)
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

    public function visitOutgoingQueuePurgeFrame(Queue\QueuePurgeFrame $frame)
    {
        $payload = "\x00\x32\x00\x1e"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->queue)
                 . ($frame->nowait ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingQueueDeleteFrame(Queue\QueueDeleteFrame $frame)
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

    public function visitOutgoingQueueUnbindFrame(Queue\QueueUnbindFrame $frame)
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

    public function visitOutgoingBasicQosFrame(Basic\BasicQosFrame $frame)
    {
        $payload = "\x00\x3c\x00\x0a"
                 . ($frame->global ? "\x01" : "\x00")
                 . pack('Nn', $frame->prefetchSize, $frame->prefetchCount)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingBasicConsumeFrame(Basic\BasicConsumeFrame $frame)
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

    public function visitOutgoingBasicCancelFrame(Basic\BasicCancelFrame $frame)
    {
        $payload = "\x00\x3c\x00\x1e"
                 . $this->serializeShortString($frame->consumerTag)
                 . ($frame->nowait ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingBasicPublishFrame(Basic\BasicPublishFrame $frame)
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

    public function visitOutgoingBasicGetFrame(Basic\BasicGetFrame $frame)
    {
        $payload = "\x00\x3c\x00\x46"
                 . pack('n', $frame->reserved1)
                 . $this->serializeShortString($frame->queue)
                 . ($frame->noAck ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingBasicAckFrame(Basic\BasicAckFrame $frame)
    {
        $payload = "\x00\x3c\x00\x50"
                 . ($frame->multiple ? "\x01" : "\x00")
                 . pack('J', $frame->deliveryTag)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingBasicRejectFrame(Basic\BasicRejectFrame $frame)
    {
        $payload = "\x00\x3c\x00\x5a"
                 . ($frame->requeue ? "\x01" : "\x00")
                 . pack('J', $frame->deliveryTag)
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingBasicRecoverAsyncFrame(Basic\BasicRecoverAsyncFrame $frame)
    {
        $payload = "\x00\x3c\x00\x64"
                 . ($frame->requeue ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingBasicRecoverFrame(Basic\BasicRecoverFrame $frame)
    {
        $payload = "\x00\x3c\x00\x6e"
                 . ($frame->requeue ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

    public function visitOutgoingBasicNackFrame(Basic\BasicNackFrame $frame)
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

    public function visitOutgoingTxSelectFrame(Tx\TxSelectFrame $frame)
    {
        return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x5a\x00\x0a\xce";
    }

    public function visitOutgoingTxCommitFrame(Tx\TxCommitFrame $frame)
    {
        return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x5a\x00\x14\xce";
    }

    public function visitOutgoingTxRollbackFrame(Tx\TxRollbackFrame $frame)
    {
        return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x5a\x00\x1e\xce";
    }

    public function visitOutgoingConfirmSelectFrame(Confirm\ConfirmSelectFrame $frame)
    {
        $payload = "\x00\x55\x00\x0a"
                 . ($frame->nowait ? "\x01" : "\x00")
                 ;

        return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
    }

}
