<?php
namespace Recoil\Amqp\Protocol;

trait MethodParserTrait
{
    private function parseMethodFrame()
    {
        list($class, $method) = array_values(unpack("n_1/n_2", $this->buffer));
        $this->buffer = substr($this->buffer, 4);

        switch ($class) {
            // class "connection"
            case 10:
                switch ($method) {
                    case 10: return $this->parseConnectionStartFrame();
                    case 11: return $this->parseConnectionStartOkFrame();
                    case 20: return $this->parseConnectionSecureFrame();
                    case 21: return $this->parseConnectionSecureOkFrame();
                    case 30: return $this->parseConnectionTuneFrame();
                    case 31: return $this->parseConnectionTuneOkFrame();
                    case 40: return $this->parseConnectionOpenFrame();
                    case 41: return $this->parseConnectionOpenOkFrame();
                    case 50: return $this->parseConnectionCloseFrame();
                    case 51: return $this->parseConnectionCloseOkFrame();
                    case 60: return $this->parseConnectionBlockedFrame();
                    case 61: return $this->parseConnectionUnblockedFrame();
                }

                throw new RuntimeException("Unknown AMQP method ID: " . $method . " in " . 'connection' . " class.");

            // class "channel"
            case 20:
                switch ($method) {
                    case 10: return $this->parseChannelOpenFrame();
                    case 11: return $this->parseChannelOpenOkFrame();
                    case 20: return $this->parseChannelFlowFrame();
                    case 21: return $this->parseChannelFlowOkFrame();
                    case 40: return $this->parseChannelCloseFrame();
                    case 41: return $this->parseChannelCloseOkFrame();
                }

                throw new RuntimeException("Unknown AMQP method ID: " . $method . " in " . 'channel' . " class.");

            // class "access"
            case 30:
                switch ($method) {
                    case 10: return $this->parseAccessRequestFrame();
                    case 11: return $this->parseAccessRequestOkFrame();
                }

                throw new RuntimeException("Unknown AMQP method ID: " . $method . " in " . 'access' . " class.");

            // class "exchange"
            case 40:
                switch ($method) {
                    case 10: return $this->parseExchangeDeclareFrame();
                    case 11: return $this->parseExchangeDeclareOkFrame();
                    case 20: return $this->parseExchangeDeleteFrame();
                    case 21: return $this->parseExchangeDeleteOkFrame();
                    case 30: return $this->parseExchangeBindFrame();
                    case 31: return $this->parseExchangeBindOkFrame();
                    case 40: return $this->parseExchangeUnbindFrame();
                    case 51: return $this->parseExchangeUnbindOkFrame();
                }

                throw new RuntimeException("Unknown AMQP method ID: " . $method . " in " . 'exchange' . " class.");

            // class "queue"
            case 50:
                switch ($method) {
                    case 10: return $this->parseQueueDeclareFrame();
                    case 11: return $this->parseQueueDeclareOkFrame();
                    case 20: return $this->parseQueueBindFrame();
                    case 21: return $this->parseQueueBindOkFrame();
                    case 30: return $this->parseQueuePurgeFrame();
                    case 31: return $this->parseQueuePurgeOkFrame();
                    case 40: return $this->parseQueueDeleteFrame();
                    case 41: return $this->parseQueueDeleteOkFrame();
                    case 50: return $this->parseQueueUnbindFrame();
                    case 51: return $this->parseQueueUnbindOkFrame();
                }

                throw new RuntimeException("Unknown AMQP method ID: " . $method . " in " . 'queue' . " class.");

            // class "basic"
            case 60:
                switch ($method) {
                    case 10: return $this->parseBasicQosFrame();
                    case 11: return $this->parseBasicQosOkFrame();
                    case 20: return $this->parseBasicConsumeFrame();
                    case 21: return $this->parseBasicConsumeOkFrame();
                    case 30: return $this->parseBasicCancelFrame();
                    case 31: return $this->parseBasicCancelOkFrame();
                    case 40: return $this->parseBasicPublishFrame();
                    case 50: return $this->parseBasicReturnFrame();
                    case 60: return $this->parseBasicDeliverFrame();
                    case 70: return $this->parseBasicGetFrame();
                    case 71: return $this->parseBasicGetOkFrame();
                    case 72: return $this->parseBasicGetEmptyFrame();
                    case 80: return $this->parseBasicAckFrame();
                    case 90: return $this->parseBasicRejectFrame();
                    case 100: return $this->parseBasicRecoverAsyncFrame();
                    case 110: return $this->parseBasicRecoverFrame();
                    case 111: return $this->parseBasicRecoverOkFrame();
                    case 120: return $this->parseBasicNackFrame();
                }

                throw new RuntimeException("Unknown AMQP method ID: " . $method . " in " . 'basic' . " class.");

            // class "tx"
            case 90:
                switch ($method) {
                    case 10: return $this->parseTxSelectFrame();
                    case 11: return $this->parseTxSelectOkFrame();
                    case 20: return $this->parseTxCommitFrame();
                    case 21: return $this->parseTxCommitOkFrame();
                    case 30: return $this->parseTxRollbackFrame();
                    case 31: return $this->parseTxRollbackOkFrame();
                }

                throw new RuntimeException("Unknown AMQP method ID: " . $method . " in " . 'tx' . " class.");

            // class "confirm"
            case 85:
                switch ($method) {
                    case 10: return $this->parseConfirmSelectFrame();
                    case 11: return $this->parseConfirmSelectOkFrame();
                }

                throw new RuntimeException("Unknown AMQP method ID: " . $method . " in " . 'confirm' . " class.");

        }

        throw new RuntimeException("Unknown AMQP class ID: " . $class . ".");
    }

    private function parseConnectionStartFrame()
    {
        $frame = new Connection\StartFrame();

        // consume "version-major" (octet)
        // consume "version-minor" (octet)
        list($frame->versionMajor, $frame->versionMinor) = array_values(unpack('c_0/c_1', $this->buffer));
        $this->buffer = substr($this->buffer, 2);

        // consume "server-properties" (table)
        $frame->serverProperties = $this->parseTable();

        // consume "mechanisms" (longstr)
        $frame->mechanisms = $this->parseLongString();

        // consume "locales" (longstr)
        $frame->locales = $this->parseLongString();

        return $frame;
    }

    private function parseConnectionSecureFrame()
    {
        $frame = new Connection\SecureFrame();

        // consume "challenge" (longstr)
        $frame->challenge = $this->parseLongString();

        return $frame;
    }

    private function parseConnectionTuneFrame()
    {
        $frame = new Connection\TuneFrame();

        // consume "channel-max" (short)
        // consume "frame-max" (long)
        // consume "heartbeat" (short)
        list($frame->channelMax, $frame->frameMax, $frame->heartbeat) = array_values(unpack('n_0/N_1/n_2', $this->buffer));
        $this->buffer = substr($this->buffer, 8);

        return $frame;
    }

    private function parseConnectionOpenOkFrame()
    {
        $frame = new Connection\OpenOkFrame();

        // consume "known-hosts" (shortstr)
        $frame->knownHosts = $this->parseShortString();

        return $frame;
    }

    private function parseConnectionCloseFrame()
    {
        $frame = new Connection\CloseFrame();

        // consume "replyCode" (short)
        list(, $frame->replyCode) = unpack('n', $this->buffer);
        $this->buffer = substr($this->buffer, 2);

        // consume "reply-text" (shortstr)
        $frame->replyText = $this->parseShortString();

        // consume "class-id" (short)
        // consume "method-id" (short)
        list($frame->classId, $frame->methodId) = array_values(unpack('n_0/n_1', $this->buffer));
        $this->buffer = substr($this->buffer, 4);

        return $frame;
    }

    private function parseConnectionCloseOkFrame()
    {
        return new Connection\CloseOkFrame();
    }

    private function parseConnectionBlockedFrame()
    {
        $frame = new Connection\BlockedFrame();

        // consume "reason" (shortstr)
        $frame->reason = $this->parseShortString();

        return $frame;
    }

    private function parseConnectionUnblockedFrame()
    {
        return new Connection\UnblockedFrame();
    }

    private function parseChannelOpenOkFrame()
    {
        $frame = new Channel\OpenOkFrame();

        // consume "channel-id" (longstr)
        $frame->channelId = $this->parseLongString();

        return $frame;
    }

    private function parseChannelFlowFrame()
    {
        $frame = new Channel\FlowFrame();

        // consume "active" (bit)
        $frame->active = $this->buffer[0] !== "\x00";
        $this->buffer = substr($this->buffer, 1);

        return $frame;
    }

    private function parseChannelFlowOkFrame()
    {
        $frame = new Channel\FlowOkFrame();

        // consume "active" (bit)
        $frame->active = $this->buffer[0] !== "\x00";
        $this->buffer = substr($this->buffer, 1);

        return $frame;
    }

    private function parseChannelCloseFrame()
    {
        $frame = new Channel\CloseFrame();

        // consume "replyCode" (short)
        list(, $frame->replyCode) = unpack('n', $this->buffer);
        $this->buffer = substr($this->buffer, 2);

        // consume "reply-text" (shortstr)
        $frame->replyText = $this->parseShortString();

        // consume "class-id" (short)
        // consume "method-id" (short)
        list($frame->classId, $frame->methodId) = array_values(unpack('n_0/n_1', $this->buffer));
        $this->buffer = substr($this->buffer, 4);

        return $frame;
    }

    private function parseChannelCloseOkFrame()
    {
        return new Channel\CloseOkFrame();
    }

    private function parseAccessRequestOkFrame()
    {
        $frame = new Access\RequestOkFrame();

        // consume "reserved" (short)
        list(, $frame->reserved) = unpack('n', $this->buffer);
        $this->buffer = substr($this->buffer, 2);

        return $frame;
    }

    private function parseExchangeDeclareOkFrame()
    {
        return new Exchange\DeclareOkFrame();
    }

    private function parseExchangeDeleteOkFrame()
    {
        return new Exchange\DeleteOkFrame();
    }

    private function parseExchangeBindOkFrame()
    {
        return new Exchange\BindOkFrame();
    }

    private function parseExchangeUnbindOkFrame()
    {
        return new Exchange\UnbindOkFrame();
    }

    private function parseQueueDeclareOkFrame()
    {
        $frame = new Queue\DeclareOkFrame();

        // consume "queue" (shortstr)
        $frame->queue = $this->parseShortString();

        // consume "message-count" (long)
        // consume "consumer-count" (long)
        list($frame->messageCount, $frame->consumerCount) = array_values(unpack('N_0/N_1', $this->buffer));
        $this->buffer = substr($this->buffer, 8);

        return $frame;
    }

    private function parseQueueBindOkFrame()
    {
        return new Queue\BindOkFrame();
    }

    private function parseQueuePurgeOkFrame()
    {
        $frame = new Queue\PurgeOkFrame();

        // consume "messageCount" (long)
        list(, $frame->messageCount) = unpack('N', $this->buffer);
        $this->buffer = substr($this->buffer, 4);

        return $frame;
    }

    private function parseQueueDeleteOkFrame()
    {
        $frame = new Queue\DeleteOkFrame();

        // consume "messageCount" (long)
        list(, $frame->messageCount) = unpack('N', $this->buffer);
        $this->buffer = substr($this->buffer, 4);

        return $frame;
    }

    private function parseQueueUnbindOkFrame()
    {
        return new Queue\UnbindOkFrame();
    }

    private function parseBasicQosOkFrame()
    {
        return new Basic\QosOkFrame();
    }

    private function parseBasicConsumeOkFrame()
    {
        $frame = new Basic\ConsumeOkFrame();

        // consume "consumer-tag" (shortstr)
        $frame->consumerTag = $this->parseShortString();

        return $frame;
    }

    private function parseBasicCancelOkFrame()
    {
        $frame = new Basic\CancelOkFrame();

        // consume "consumer-tag" (shortstr)
        $frame->consumerTag = $this->parseShortString();

        return $frame;
    }

    private function parseBasicReturnFrame()
    {
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
    }

    private function parseBasicDeliverFrame()
    {
        $frame = new Basic\DeliverFrame();

        // consume "consumer-tag" (shortstr)
        $frame->consumerTag = $this->parseShortString();

        // consume "redelivered" (bit)
        $frame->redelivered = $this->buffer[0] !== "\x00";
        $this->buffer = substr($this->buffer, 1);

        // consume "deliveryTag" (longlong)
        list(, $frame->deliveryTag) = unpack('J', $this->buffer);
        $this->buffer = substr($this->buffer, 8);

        // consume "exchange" (shortstr)
        $frame->exchange = $this->parseShortString();

        // consume "routing-key" (shortstr)
        $frame->routingKey = $this->parseShortString();

        return $frame;
    }

    private function parseBasicGetOkFrame()
    {
        $frame = new Basic\GetOkFrame();

        // consume "redelivered" (bit)
        $frame->redelivered = $this->buffer[0] !== "\x00";
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
    }

    private function parseBasicGetEmptyFrame()
    {
        $frame = new Basic\GetEmptyFrame();

        // consume "cluster-id" (shortstr)
        $frame->clusterId = $this->parseShortString();

        return $frame;
    }

    private function parseBasicAckFrame()
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

    private function parseBasicRecoverOkFrame()
    {
        return new Basic\RecoverOkFrame();
    }

    private function parseBasicNackFrame()
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

    private function parseTxSelectOkFrame()
    {
        return new Tx\SelectOkFrame();
    }

    private function parseTxCommitOkFrame()
    {
        return new Tx\CommitOkFrame();
    }

    private function parseTxRollbackOkFrame()
    {
        return new Tx\RollbackOkFrame();
    }

    private function parseConfirmSelectOkFrame()
    {
        return new Confirm\SelectOkFrame();
    }
}
