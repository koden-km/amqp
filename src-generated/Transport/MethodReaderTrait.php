<?php
namespace Recoil\Amqp\Transport;

trait MethodReaderTrait
{
    //
    // AMQP class 10 - connection
    //

    private function readConnectionStart()
    {
        $result = new Connection\StartMethod()

        // consume "version-major" (octet)
        // consume "version-minor" (octet)
        list($frame->versionMajor, $frame->versionMinor) = array_values(unpack('c_0/c_1', $this->buffer));
        $this->buffer = substr($this->buffer, 2) ?: "";

        // consume "server-properties" (table)
        // not supported yet - table

        // consume "mechanisms" (longstr)
        list(, $length) = unpack("N", $this->buffer);
        $frame->mechanisms = substr($this->buffer, 4, $length);
        $this->buffer = substr($this->buffer, $length + 4) ?: "";

        // consume "locales" (longstr)
        list(, $length) = unpack("N", $this->buffer);
        $frame->locales = substr($this->buffer, 4, $length);
        $this->buffer = substr($this->buffer, $length + 4) ?: "";

        return $result;
    }

    private function readConnectionSecure()
    {
        $result = new Connection\SecureMethod()

        // consume "challenge" (longstr)
        list(, $length) = unpack("N", $this->buffer);
        $frame->challenge = substr($this->buffer, 4, $length);
        $this->buffer = substr($this->buffer, $length + 4) ?: "";

        return $result;
    }

    private function readConnectionTune()
    {
        $result = new Connection\TuneMethod()

        // consume "channel-max" (short)
        // consume "frame-max" (long)
        // consume "heartbeat" (short)
        list($frame->channelMax, $frame->frameMax, $frame->heartbeat) = array_values(unpack('n_0/N_1/n_2', $this->buffer));
        $this->buffer = substr($this->buffer, 8) ?: "";

        return $result;
    }

    private function readConnectionOpenOk()
    {
        $result = new Connection\OpenOkMethod()

        // consume "known-hosts" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->knownHosts = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        return $result;
    }

    private function readConnectionClose()
    {
        $result = new Connection\CloseMethod()

        list(, $frame->replyCode) = unpack('n', $this->buffer);
        $this->buffer = substr($this->buffer, 2) ?: "";

        // consume "reply-text" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->replyText = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        // consume "class-id" (short)
        // consume "method-id" (short)
        list($frame->classId, $frame->methodId) = array_values(unpack('n_0/n_1', $this->buffer));
        $this->buffer = substr($this->buffer, 4) ?: "";

        return $result;
    }

    private function readConnectionCloseOk()
    {
        $result = new Connection\CloseOkMethod()

        return $result;
    }

    private function readConnectionBlocked()
    {
        $result = new Connection\BlockedMethod()

        // consume "reason" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->reason = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        return $result;
    }

    private function readConnectionUnblocked()
    {
        $result = new Connection\UnblockedMethod()

        return $result;
    }

    //
    // AMQP class 20 - channel
    //

    private function readChannelOpenOk()
    {
        $result = new Channel\OpenOkMethod()

        // consume "channel-id" (longstr)
        list(, $length) = unpack("N", $this->buffer);
        $frame->channelId = substr($this->buffer, 4, $length);
        $this->buffer = substr($this->buffer, $length + 4) ?: "";

        return $result;
    }

    private function readChannelFlow()
    {
        $result = new Channel\FlowMethod()

        // consume "active" (bit)
        $result->active = $this->buffer[0] !== "\0";
        $this->buffer = substr($this->buffer, 1) ?: "";

        return $result;
    }

    private function readChannelFlowOk()
    {
        $result = new Channel\FlowOkMethod()

        // consume "active" (bit)
        $result->active = $this->buffer[0] !== "\0";
        $this->buffer = substr($this->buffer, 1) ?: "";

        return $result;
    }

    private function readChannelClose()
    {
        $result = new Channel\CloseMethod()

        list(, $frame->replyCode) = unpack('n', $this->buffer);
        $this->buffer = substr($this->buffer, 2) ?: "";

        // consume "reply-text" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->replyText = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        // consume "class-id" (short)
        // consume "method-id" (short)
        list($frame->classId, $frame->methodId) = array_values(unpack('n_0/n_1', $this->buffer));
        $this->buffer = substr($this->buffer, 4) ?: "";

        return $result;
    }

    private function readChannelCloseOk()
    {
        $result = new Channel\CloseOkMethod()

        return $result;
    }

    //
    // AMQP class 30 - access
    //

    private function readAccessRequestOk()
    {
        $result = new Access\RequestOkMethod()

        list(, $frame->reserved) = unpack('n', $this->buffer);
        $this->buffer = substr($this->buffer, 2) ?: "";

        return $result;
    }

    //
    // AMQP class 40 - exchange
    //

    private function readExchangeDeclareOk()
    {
        $result = new Exchange\DeclareOkMethod()

        return $result;
    }

    private function readExchangeDeleteOk()
    {
        $result = new Exchange\DeleteOkMethod()

        return $result;
    }

    private function readExchangeBindOk()
    {
        $result = new Exchange\BindOkMethod()

        return $result;
    }

    private function readExchangeUnbindOk()
    {
        $result = new Exchange\UnbindOkMethod()

        return $result;
    }

    //
    // AMQP class 50 - queue
    //

    private function readQueueDeclareOk()
    {
        $result = new Queue\DeclareOkMethod()

        // consume "queue" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->queue = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        // consume "message-count" (long)
        // consume "consumer-count" (long)
        list($frame->messageCount, $frame->consumerCount) = array_values(unpack('N_0/N_1', $this->buffer));
        $this->buffer = substr($this->buffer, 8) ?: "";

        return $result;
    }

    private function readQueueBindOk()
    {
        $result = new Queue\BindOkMethod()

        return $result;
    }

    private function readQueuePurgeOk()
    {
        $result = new Queue\PurgeOkMethod()

        list(, $frame->messageCount) = unpack('N', $this->buffer);
        $this->buffer = substr($this->buffer, 4) ?: "";

        return $result;
    }

    private function readQueueDeleteOk()
    {
        $result = new Queue\DeleteOkMethod()

        list(, $frame->messageCount) = unpack('N', $this->buffer);
        $this->buffer = substr($this->buffer, 4) ?: "";

        return $result;
    }

    private function readQueueUnbindOk()
    {
        $result = new Queue\UnbindOkMethod()

        return $result;
    }

    //
    // AMQP class 60 - basic
    //

    private function readBasicQosOk()
    {
        $result = new Basic\QosOkMethod()

        return $result;
    }

    private function readBasicConsumeOk()
    {
        $result = new Basic\ConsumeOkMethod()

        // consume "consumer-tag" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->consumerTag = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        return $result;
    }

    private function readBasicCancelOk()
    {
        $result = new Basic\CancelOkMethod()

        // consume "consumer-tag" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->consumerTag = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        return $result;
    }

    private function readBasicReturn()
    {
        $result = new Basic\ReturnMethod()

        list(, $frame->replyCode) = unpack('n', $this->buffer);
        $this->buffer = substr($this->buffer, 2) ?: "";

        // consume "reply-text" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->replyText = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        // consume "exchange" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->exchange = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        // consume "routing-key" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->routingKey = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        return $result;
    }

    private function readBasicDeliver()
    {
        $result = new Basic\DeliverMethod()

        // consume "consumer-tag" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->consumerTag = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        // consume "redelivered" (bit)
        $result->redelivered = $this->buffer[0] !== "\0";
        $this->buffer = substr($this->buffer, 1) ?: "";

        list(, $frame->deliveryTag) = unpack('J', $this->buffer);
        $this->buffer = substr($this->buffer, 8) ?: "";

        // consume "exchange" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->exchange = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        // consume "routing-key" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->routingKey = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        return $result;
    }

    private function readBasicGetOk()
    {
        $result = new Basic\GetOkMethod()

        // consume "redelivered" (bit)
        $result->redelivered = $this->buffer[0] !== "\0";
        $this->buffer = substr($this->buffer, 1) ?: "";

        list(, $frame->deliveryTag) = unpack('J', $this->buffer);
        $this->buffer = substr($this->buffer, 8) ?: "";

        // consume "exchange" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->exchange = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        // consume "routing-key" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->routingKey = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        list(, $frame->messageCount) = unpack('N', $this->buffer);
        $this->buffer = substr($this->buffer, 4) ?: "";

        return $result;
    }

    private function readBasicGetEmpty()
    {
        $result = new Basic\GetEmptyMethod()

        // consume "cluster-id" (shortstr)
        $length = ord($this->buffer[0]);
        $frame->clusterId = substr($this->buffer, 1, $length);
        $this->buffer = substr($this->buffer, $length + 1) ?: "";

        return $result;
    }

    private function readBasicAck()
    {
        $result = new Basic\AckMethod()

        // consume "multiple" (bit)
        $result->multiple = $this->buffer[0] !== "\0";
        $this->buffer = substr($this->buffer, 1) ?: "";

        list(, $frame->deliveryTag) = unpack('J', $this->buffer);
        $this->buffer = substr($this->buffer, 8) ?: "";

        return $result;
    }

    private function readBasicRecoverOk()
    {
        $result = new Basic\RecoverOkMethod()

        return $result;
    }

    private function readBasicNack()
    {
        $result = new Basic\NackMethod()

        // consume "multiple" (bit)
        // consume "requeue" (bit)
        $octet = ord($this->buffer[0]);
        $frame->multiple = $octet & 1 !== 0;
        $frame->requeue = $octet & 2 !== 0;
        $this->buffer = substr($this->buffer, 1) ?: "";

        list(, $frame->deliveryTag) = unpack('J', $this->buffer);
        $this->buffer = substr($this->buffer, 8) ?: "";

        return $result;
    }

    //
    // AMQP class 90 - tx
    //

    private function readTxSelectOk()
    {
        $result = new Tx\SelectOkMethod()

        return $result;
    }

    private function readTxCommitOk()
    {
        $result = new Tx\CommitOkMethod()

        return $result;
    }

    private function readTxRollbackOk()
    {
        $result = new Tx\RollbackOkMethod()

        return $result;
    }

    //
    // AMQP class 85 - confirm
    //

    private function readConfirmSelectOk()
    {
        $result = new Confirm\SelectOkMethod()

        return $result;
    }

}
