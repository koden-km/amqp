<?php
namespace Recoil\Amqp\v091\Protocol;

trait FrameSerializerTrait
{
    public function serialize(OutgoingFrame $frame)
    {
        if ($frame instanceof HeartbeatFrame) {
            return $this->serializeHeartbeatFrame();
        } elseif ($frame instanceof Connection\ConnectionStartOkFrame) {
            $payload = "\x00\x0a\x00\x0b"
                     . $this->serializeTable($frame->clientProperties)
                     . $this->serializeShortString($frame->mechanism)
                     . $this->serializeLongString($frame->response)
                     . $this->serializeShortString($frame->locale)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Connection\ConnectionSecureOkFrame) {
            $payload = "\x00\x0a\x00\x15"
                     . $this->serializeLongString($frame->response)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Connection\ConnectionTuneOkFrame) {
            $payload = "\x00\x0a\x00\x1f"
                     . pack('nNn', $frame->channelMax, $frame->frameMax, $frame->heartbeat)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Connection\ConnectionOpenFrame) {
            $payload = "\x00\x0a\x00\x28"
                     . $this->serializeShortString($frame->virtualHost)
                     . $this->serializeShortString($frame->capabilities)
                     . ($frame->insist ? "\x01" : "\x00")
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Connection\ConnectionCloseFrame) {
            $payload = "\x00\x0a\x00\x32"
                     . pack('n', $frame->replyCode)
                     . $this->serializeShortString($frame->replyText)
                     . pack('nn', $frame->classId, $frame->methodId)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Connection\ConnectionCloseOkFrame) {
            return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x0a\x00\x33\xce";
        } elseif ($frame instanceof Channel\ChannelOpenFrame) {
            $payload = "\x00\x14\x00\x0a"
                     . $this->serializeShortString($frame->outOfBand)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Channel\ChannelFlowFrame) {
            $payload = "\x00\x14\x00\x14"
                     . ($frame->active ? "\x01" : "\x00")
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Channel\ChannelFlowOkFrame) {
            $payload = "\x00\x14\x00\x15"
                     . ($frame->active ? "\x01" : "\x00")
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Channel\ChannelCloseFrame) {
            $payload = "\x00\x14\x00\x28"
                     . pack('n', $frame->replyCode)
                     . $this->serializeShortString($frame->replyText)
                     . pack('nn', $frame->classId, $frame->methodId)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Channel\ChannelCloseOkFrame) {
            return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x14\x00\x29\xce";
        } elseif ($frame instanceof Access\AccessRequestFrame) {
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
        } elseif ($frame instanceof Exchange\ExchangeDeclareFrame) {
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
        } elseif ($frame instanceof Exchange\ExchangeDeleteFrame) {
            $payload = "\x00\x28\x00\x14"
                     . pack('n', $frame->reserved1)
                     . $this->serializeShortString($frame->exchange)
                     . chr(
                           $frame->ifUnused
                         | $frame->nowait << 1
                     )
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Exchange\ExchangeBindFrame) {
            $payload = "\x00\x28\x00\x1e"
                     . pack('n', $frame->reserved1)
                     . $this->serializeShortString($frame->destination)
                     . $this->serializeShortString($frame->source)
                     . $this->serializeShortString($frame->routingKey)
                     . ($frame->nowait ? "\x01" : "\x00")
                     . $this->serializeTable($frame->arguments)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Exchange\ExchangeUnbindFrame) {
            $payload = "\x00\x28\x00\x28"
                     . pack('n', $frame->reserved1)
                     . $this->serializeShortString($frame->destination)
                     . $this->serializeShortString($frame->source)
                     . $this->serializeShortString($frame->routingKey)
                     . ($frame->nowait ? "\x01" : "\x00")
                     . $this->serializeTable($frame->arguments)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Queue\QueueDeclareFrame) {
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
        } elseif ($frame instanceof Queue\QueueBindFrame) {
            $payload = "\x00\x32\x00\x14"
                     . pack('n', $frame->reserved1)
                     . $this->serializeShortString($frame->queue)
                     . $this->serializeShortString($frame->exchange)
                     . $this->serializeShortString($frame->routingKey)
                     . ($frame->nowait ? "\x01" : "\x00")
                     . $this->serializeTable($frame->arguments)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Queue\QueuePurgeFrame) {
            $payload = "\x00\x32\x00\x1e"
                     . pack('n', $frame->reserved1)
                     . $this->serializeShortString($frame->queue)
                     . ($frame->nowait ? "\x01" : "\x00")
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Queue\QueueDeleteFrame) {
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
        } elseif ($frame instanceof Queue\QueueUnbindFrame) {
            $payload = "\x00\x32\x00\x32"
                     . pack('n', $frame->reserved1)
                     . $this->serializeShortString($frame->queue)
                     . $this->serializeShortString($frame->exchange)
                     . $this->serializeShortString($frame->routingKey)
                     . $this->serializeTable($frame->arguments)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Basic\BasicQosFrame) {
            $payload = "\x00\x3c\x00\x0a"
                     . ($frame->global ? "\x01" : "\x00")
                     . pack('Nn', $frame->prefetchSize, $frame->prefetchCount)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Basic\BasicConsumeFrame) {
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
        } elseif ($frame instanceof Basic\BasicCancelFrame) {
            $payload = "\x00\x3c\x00\x1e"
                     . $this->serializeShortString($frame->consumerTag)
                     . ($frame->nowait ? "\x01" : "\x00")
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Basic\BasicPublishFrame) {
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
        } elseif ($frame instanceof Basic\BasicGetFrame) {
            $payload = "\x00\x3c\x00\x46"
                     . pack('n', $frame->reserved1)
                     . $this->serializeShortString($frame->queue)
                     . ($frame->noAck ? "\x01" : "\x00")
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Basic\BasicAckFrame) {
            $payload = "\x00\x3c\x00\x50"
                     . ($frame->multiple ? "\x01" : "\x00")
                     . pack('J', $frame->deliveryTag)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Basic\BasicRejectFrame) {
            $payload = "\x00\x3c\x00\x5a"
                     . ($frame->requeue ? "\x01" : "\x00")
                     . pack('J', $frame->deliveryTag)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Basic\BasicRecoverAsyncFrame) {
            $payload = "\x00\x3c\x00\x64"
                     . ($frame->requeue ? "\x01" : "\x00")
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Basic\BasicRecoverFrame) {
            $payload = "\x00\x3c\x00\x6e"
                     . ($frame->requeue ? "\x01" : "\x00")
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Basic\BasicNackFrame) {
            $payload = "\x00\x3c\x00\x78"
                     . chr(
                           $frame->multiple
                         | $frame->requeue << 1
                     )
                     . pack('J', $frame->deliveryTag)
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        } elseif ($frame instanceof Tx\TxSelectFrame) {
            return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x5a\x00\x0a\xce";
        } elseif ($frame instanceof Tx\TxCommitFrame) {
            return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x5a\x00\x14\xce";
        } elseif ($frame instanceof Tx\TxRollbackFrame) {
            return "\x01" . pack("n", $frame->channel) . "\x00\x00\x00\x08\x00\x5a\x00\x1e\xce";
        } elseif ($frame instanceof Confirm\ConfirmSelectFrame) {
            $payload = "\x00\x55\x00\x0a"
                     . ($frame->nowait ? "\x01" : "\x00")
                     ;

            return "\x01" . pack("nN", $frame->channel, strlen($payload)) . $payload . "\xce";
        }
    }
}
