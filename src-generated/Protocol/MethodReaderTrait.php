<?php
namespace Recoil\Amqp\Protocol;

trait MethodReaderTrait
{
    private function readMethodFrame($channel)
    {
        list($class, $method) = array_values(unpack("n_1/n_2", $this->buffer));
        $this->buffer = substr($this->buffer, 4);

        switch ($class) {
            // class "connection"
            case 10:
                switch ($method) {
                    case 10: return $this->readConnectionStartFrame();
                    case 11: return $this->readConnectionStartOkFrame();
                    case 20: return $this->readConnectionSecureFrame();
                    case 21: return $this->readConnectionSecureOkFrame();
                    case 30: return $this->readConnectionTuneFrame();
                    case 31: return $this->readConnectionTuneOkFrame();
                    case 40: return $this->readConnectionOpenFrame();
                    case 41: return $this->readConnectionOpenOkFrame();
                    case 50: return $this->readConnectionCloseFrame();
                    case 51: return $this->readConnectionCloseOkFrame();
                    case 60: return $this->readConnectionBlockedFrame();
                    case 61: return $this->readConnectionUnblockedFrame();
                    default:
                        throw new RuntimeException(
                            'AMQP class "connection" does not have a method with ID ' . $method . '.'
                        );
                }

            // class "channel"
            case 20:
                switch ($method) {
                    case 10: return $this->readChannelOpenFrame();
                    case 11: return $this->readChannelOpenOkFrame();
                    case 20: return $this->readChannelFlowFrame();
                    case 21: return $this->readChannelFlowOkFrame();
                    case 40: return $this->readChannelCloseFrame();
                    case 41: return $this->readChannelCloseOkFrame();
                    default:
                        throw new RuntimeException(
                            'AMQP class "channel" does not have a method with ID ' . $method . '.'
                        );
                }

            // class "access"
            case 30:
                switch ($method) {
                    case 10: return $this->readAccessRequestFrame();
                    case 11: return $this->readAccessRequestOkFrame();
                    default:
                        throw new RuntimeException(
                            'AMQP class "access" does not have a method with ID ' . $method . '.'
                        );
                }

            // class "exchange"
            case 40:
                switch ($method) {
                    case 10: return $this->readExchangeDeclareFrame();
                    case 11: return $this->readExchangeDeclareOkFrame();
                    case 20: return $this->readExchangeDeleteFrame();
                    case 21: return $this->readExchangeDeleteOkFrame();
                    case 30: return $this->readExchangeBindFrame();
                    case 31: return $this->readExchangeBindOkFrame();
                    case 40: return $this->readExchangeUnbindFrame();
                    case 51: return $this->readExchangeUnbindOkFrame();
                    default:
                        throw new RuntimeException(
                            'AMQP class "exchange" does not have a method with ID ' . $method . '.'
                        );
                }

            // class "queue"
            case 50:
                switch ($method) {
                    case 10: return $this->readQueueDeclareFrame();
                    case 11: return $this->readQueueDeclareOkFrame();
                    case 20: return $this->readQueueBindFrame();
                    case 21: return $this->readQueueBindOkFrame();
                    case 30: return $this->readQueuePurgeFrame();
                    case 31: return $this->readQueuePurgeOkFrame();
                    case 40: return $this->readQueueDeleteFrame();
                    case 41: return $this->readQueueDeleteOkFrame();
                    case 50: return $this->readQueueUnbindFrame();
                    case 51: return $this->readQueueUnbindOkFrame();
                    default:
                        throw new RuntimeException(
                            'AMQP class "queue" does not have a method with ID ' . $method . '.'
                        );
                }

            // class "basic"
            case 60:
                switch ($method) {
                    case 10: return $this->readBasicQosFrame();
                    case 11: return $this->readBasicQosOkFrame();
                    case 20: return $this->readBasicConsumeFrame();
                    case 21: return $this->readBasicConsumeOkFrame();
                    case 30: return $this->readBasicCancelFrame();
                    case 31: return $this->readBasicCancelOkFrame();
                    case 40: return $this->readBasicPublishFrame();
                    case 50: return $this->readBasicReturnFrame();
                    case 60: return $this->readBasicDeliverFrame();
                    case 70: return $this->readBasicGetFrame();
                    case 71: return $this->readBasicGetOkFrame();
                    case 72: return $this->readBasicGetEmptyFrame();
                    case 80: return $this->readBasicAckFrame();
                    case 90: return $this->readBasicRejectFrame();
                    case 100: return $this->readBasicRecoverAsyncFrame();
                    case 110: return $this->readBasicRecoverFrame();
                    case 111: return $this->readBasicRecoverOkFrame();
                    case 120: return $this->readBasicNackFrame();
                    default:
                        throw new RuntimeException(
                            'AMQP class "basic" does not have a method with ID ' . $method . '.'
                        );
                }

            // class "tx"
            case 90:
                switch ($method) {
                    case 10: return $this->readTxSelectFrame();
                    case 11: return $this->readTxSelectOkFrame();
                    case 20: return $this->readTxCommitFrame();
                    case 21: return $this->readTxCommitOkFrame();
                    case 30: return $this->readTxRollbackFrame();
                    case 31: return $this->readTxRollbackOkFrame();
                    default:
                        throw new RuntimeException(
                            'AMQP class "tx" does not have a method with ID ' . $method . '.'
                        );
                }

            // class "confirm"
            case 85:
                switch ($method) {
                    case 10: return $this->readConfirmSelectFrame();
                    case 11: return $this->readConfirmSelectOkFrame();
                    default:
                        throw new RuntimeException(
                            'AMQP class "confirm" does not have a method with ID ' . $method . '.'
                        );
                }

            default:
                throw new RuntimeException(
                    'AMQP class "confirm" does not have a method with ID ' . $method . '.'
                );
        }
    }

    private function readConnectionStartFrame()
    {
        $frame = new Connection\StartFrame();

        // consume "version-major" (octet)
        // consume "version-minor" (octet)
        list($frame->versionMajor, $frame->versionMinor) = array_values(unpack('c_0/c_1', $this->buffer));
        $this->buffer = substr($this->buffer, 2);

        // consume "server-properties" (table)
        $frame->serverProperties = $this->readTable();

        // consume "mechanisms" (longstr)
        $frame->mechanisms = $this->readLongString();

        // consume "locales" (longstr)
        $frame->locales = $this->readLongString();

        return $frame;
    }

    private function readConnectionSecureFrame()
    {
        $frame = new Connection\SecureFrame();

        // consume "challenge" (longstr)
        $frame->challenge = $this->readLongString();

        return $frame;
    }

    private function readConnectionTuneFrame()
    {
        $frame = new Connection\TuneFrame();

        // consume "channel-max" (short)
        // consume "frame-max" (long)
        // consume "heartbeat" (short)
        list($frame->channelMax, $frame->frameMax, $frame->heartbeat) = array_values(unpack('n_0/N_1/n_2', $this->buffer));
        $this->buffer = substr($this->buffer, 8);

        return $frame;
    }

    private function readConnectionOpenOkFrame()
    {
        $frame = new Connection\OpenOkFrame();

        // consume "known-hosts" (shortstr)
        $frame->knownHosts = $this->readShortString();

        return $frame;
    }

    private function readConnectionCloseFrame()
    {
        $frame = new Connection\CloseFrame();

        // consume "replyCode" (short)
        list(, $frame->replyCode) = unpack('n', $this->buffer);
        $this->buffer = substr($this->buffer, 2);

        // consume "reply-text" (shortstr)
        $frame->replyText = $this->readShortString();

        // consume "class-id" (short)
        // consume "method-id" (short)
        list($frame->classId, $frame->methodId) = array_values(unpack('n_0/n_1', $this->buffer));
        $this->buffer = substr($this->buffer, 4);

        return $frame;
    }

    private function readConnectionCloseOkFrame()
    {
        return new Connection\CloseOkFrame();
    }

    private function readConnectionBlockedFrame()
    {
        $frame = new Connection\BlockedFrame();

        // consume "reason" (shortstr)
        $frame->reason = $this->readShortString();

        return $frame;
    }

    private function readConnectionUnblockedFrame()
    {
        return new Connection\UnblockedFrame();
    }

    private function readChannelOpenOkFrame()
    {
        $frame = new Channel\OpenOkFrame();

        // consume "channel-id" (longstr)
        $frame->channelId = $this->readLongString();

        return $frame;
    }

    private function readChannelFlowFrame()
    {
        $frame = new Channel\FlowFrame();

        // consume "active" (bit)
        $frame->active = $this->buffer[0] !== "\x00";
        $this->buffer = substr($this->buffer, 1);

        return $frame;
    }

    private function readChannelFlowOkFrame()
    {
        $frame = new Channel\FlowOkFrame();

        // consume "active" (bit)
        $frame->active = $this->buffer[0] !== "\x00";
        $this->buffer = substr($this->buffer, 1);

        return $frame;
    }

    private function readChannelCloseFrame()
    {
        $frame = new Channel\CloseFrame();

        // consume "replyCode" (short)
        list(, $frame->replyCode) = unpack('n', $this->buffer);
        $this->buffer = substr($this->buffer, 2);

        // consume "reply-text" (shortstr)
        $frame->replyText = $this->readShortString();

        // consume "class-id" (short)
        // consume "method-id" (short)
        list($frame->classId, $frame->methodId) = array_values(unpack('n_0/n_1', $this->buffer));
        $this->buffer = substr($this->buffer, 4);

        return $frame;
    }

    private function readChannelCloseOkFrame()
    {
        return new Channel\CloseOkFrame();
    }

    private function readAccessRequestOkFrame()
    {
        $frame = new Access\RequestOkFrame();

        // consume "reserved" (short)
        list(, $frame->reserved) = unpack('n', $this->buffer);
        $this->buffer = substr($this->buffer, 2);

        return $frame;
    }

    private function readExchangeDeclareOkFrame()
    {
        return new Exchange\DeclareOkFrame();
    }

    private function readExchangeDeleteOkFrame()
    {
        return new Exchange\DeleteOkFrame();
    }

    private function readExchangeBindOkFrame()
    {
        return new Exchange\BindOkFrame();
    }

    private function readExchangeUnbindOkFrame()
    {
        return new Exchange\UnbindOkFrame();
    }

    private function readQueueDeclareOkFrame()
    {
        $frame = new Queue\DeclareOkFrame();

        // consume "queue" (shortstr)
        $frame->queue = $this->readShortString();

        // consume "message-count" (long)
        // consume "consumer-count" (long)
        list($frame->messageCount, $frame->consumerCount) = array_values(unpack('N_0/N_1', $this->buffer));
        $this->buffer = substr($this->buffer, 8);

        return $frame;
    }

    private function readQueueBindOkFrame()
    {
        return new Queue\BindOkFrame();
    }

    private function readQueuePurgeOkFrame()
    {
        $frame = new Queue\PurgeOkFrame();

        // consume "messageCount" (long)
        list(, $frame->messageCount) = unpack('N', $this->buffer);
        $this->buffer = substr($this->buffer, 4);

        return $frame;
    }

    private function readQueueDeleteOkFrame()
    {
        $frame = new Queue\DeleteOkFrame();

        // consume "messageCount" (long)
        list(, $frame->messageCount) = unpack('N', $this->buffer);
        $this->buffer = substr($this->buffer, 4);

        return $frame;
    }

    private function readQueueUnbindOkFrame()
    {
        return new Queue\UnbindOkFrame();
    }

    private function readBasicQosOkFrame()
    {
        return new Basic\QosOkFrame();
    }

    private function readBasicConsumeOkFrame()
    {
        $frame = new Basic\ConsumeOkFrame();

        // consume "consumer-tag" (shortstr)
        $frame->consumerTag = $this->readShortString();

        return $frame;
    }

    private function readBasicCancelOkFrame()
    {
        $frame = new Basic\CancelOkFrame();

        // consume "consumer-tag" (shortstr)
        $frame->consumerTag = $this->readShortString();

        return $frame;
    }

    private function readBasicReturnFrame()
    {
        $frame = new Basic\ReturnFrame();

        // consume "replyCode" (short)
        list(, $frame->replyCode) = unpack('n', $this->buffer);
        $this->buffer = substr($this->buffer, 2);

        // consume "reply-text" (shortstr)
        $frame->replyText = $this->readShortString();

        // consume "exchange" (shortstr)
        $frame->exchange = $this->readShortString();

        // consume "routing-key" (shortstr)
        $frame->routingKey = $this->readShortString();

        return $frame;
    }

    private function readBasicDeliverFrame()
    {
        $frame = new Basic\DeliverFrame();

        // consume "consumer-tag" (shortstr)
        $frame->consumerTag = $this->readShortString();

        // consume "redelivered" (bit)
        $frame->redelivered = $this->buffer[0] !== "\x00";
        $this->buffer = substr($this->buffer, 1);

        // consume "deliveryTag" (longlong)
        list(, $frame->deliveryTag) = unpack('J', $this->buffer);
        $this->buffer = substr($this->buffer, 8);

        // consume "exchange" (shortstr)
        $frame->exchange = $this->readShortString();

        // consume "routing-key" (shortstr)
        $frame->routingKey = $this->readShortString();

        return $frame;
    }

    private function readBasicGetOkFrame()
    {
        $frame = new Basic\GetOkFrame();

        // consume "redelivered" (bit)
        $frame->redelivered = $this->buffer[0] !== "\x00";
        $this->buffer = substr($this->buffer, 1);

        // consume "deliveryTag" (longlong)
        list(, $frame->deliveryTag) = unpack('J', $this->buffer);
        $this->buffer = substr($this->buffer, 8);

        // consume "exchange" (shortstr)
        $frame->exchange = $this->readShortString();

        // consume "routing-key" (shortstr)
        $frame->routingKey = $this->readShortString();

        // consume "messageCount" (long)
        list(, $frame->messageCount) = unpack('N', $this->buffer);
        $this->buffer = substr($this->buffer, 4);

        return $frame;
    }

    private function readBasicGetEmptyFrame()
    {
        $frame = new Basic\GetEmptyFrame();

        // consume "cluster-id" (shortstr)
        $frame->clusterId = $this->readShortString();

        return $frame;
    }

    private function readBasicAckFrame()
    {
        $frame = new Basic\AckFrame();

        // consume "multiple" (bit)
        $frame->multiple = $this->buffer[0] !== "\x00";
        $this->buffer = substr($this->buffer, 1);

        // consume "deliveryTag" (longlong)
        list(, $frame->deliveryTag) = unpack('J', $this->buffer);
        $this->buffer = substr($this->buffer, 8);

        return $frame;
    }

    private function readBasicRecoverOkFrame()
    {
        return new Basic\RecoverOkFrame();
    }

    private function readBasicNackFrame()
    {
        $frame = new Basic\NackFrame();

        // consume "multiple" (bit)
        // consume "requeue" (bit)
        $octet = ord($this->buffer[0]);
        $frame->multiple = $octet & 1 !== 0;
        $frame->requeue = $octet & 2 !== 0;
        $this->buffer = substr($this->buffer, 1);

        // consume "deliveryTag" (longlong)
        list(, $frame->deliveryTag) = unpack('J', $this->buffer);
        $this->buffer = substr($this->buffer, 8);

        return $frame;
    }

    private function readTxSelectOkFrame()
    {
        return new Tx\SelectOkFrame();
    }

    private function readTxCommitOkFrame()
    {
        return new Tx\CommitOkFrame();
    }

    private function readTxRollbackOkFrame()
    {
        return new Tx\RollbackOkFrame();
    }

    private function readConfirmSelectOkFrame()
    {
        return new Confirm\SelectOkFrame();
    }
}
