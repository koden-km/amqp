<?php
namespace Recoil\Amqp\v091\Protocol;

interface OutgoingFrameVisitor
{
    public function visitOutgoingHeartbeatFrame(HeartbeatFrame $frame);
    public function visitOutgoingConnectionStartOkFrame(Connection\ConnectionStartOkFrame $frame);
    public function visitOutgoingConnectionSecureOkFrame(Connection\ConnectionSecureOkFrame $frame);
    public function visitOutgoingConnectionTuneOkFrame(Connection\ConnectionTuneOkFrame $frame);
    public function visitOutgoingConnectionOpenFrame(Connection\ConnectionOpenFrame $frame);
    public function visitOutgoingConnectionCloseFrame(Connection\ConnectionCloseFrame $frame);
    public function visitOutgoingConnectionCloseOkFrame(Connection\ConnectionCloseOkFrame $frame);
    public function visitOutgoingChannelOpenFrame(Channel\ChannelOpenFrame $frame);
    public function visitOutgoingChannelFlowFrame(Channel\ChannelFlowFrame $frame);
    public function visitOutgoingChannelFlowOkFrame(Channel\ChannelFlowOkFrame $frame);
    public function visitOutgoingChannelCloseFrame(Channel\ChannelCloseFrame $frame);
    public function visitOutgoingChannelCloseOkFrame(Channel\ChannelCloseOkFrame $frame);
    public function visitOutgoingAccessRequestFrame(Access\AccessRequestFrame $frame);
    public function visitOutgoingExchangeDeclareFrame(Exchange\ExchangeDeclareFrame $frame);
    public function visitOutgoingExchangeDeleteFrame(Exchange\ExchangeDeleteFrame $frame);
    public function visitOutgoingExchangeBindFrame(Exchange\ExchangeBindFrame $frame);
    public function visitOutgoingExchangeUnbindFrame(Exchange\ExchangeUnbindFrame $frame);
    public function visitOutgoingQueueDeclareFrame(Queue\QueueDeclareFrame $frame);
    public function visitOutgoingQueueBindFrame(Queue\QueueBindFrame $frame);
    public function visitOutgoingQueuePurgeFrame(Queue\QueuePurgeFrame $frame);
    public function visitOutgoingQueueDeleteFrame(Queue\QueueDeleteFrame $frame);
    public function visitOutgoingQueueUnbindFrame(Queue\QueueUnbindFrame $frame);
    public function visitOutgoingBasicQosFrame(Basic\BasicQosFrame $frame);
    public function visitOutgoingBasicConsumeFrame(Basic\BasicConsumeFrame $frame);
    public function visitOutgoingBasicCancelFrame(Basic\BasicCancelFrame $frame);
    public function visitOutgoingBasicPublishFrame(Basic\BasicPublishFrame $frame);
    public function visitOutgoingBasicGetFrame(Basic\BasicGetFrame $frame);
    public function visitOutgoingBasicAckFrame(Basic\BasicAckFrame $frame);
    public function visitOutgoingBasicRejectFrame(Basic\BasicRejectFrame $frame);
    public function visitOutgoingBasicRecoverAsyncFrame(Basic\BasicRecoverAsyncFrame $frame);
    public function visitOutgoingBasicRecoverFrame(Basic\BasicRecoverFrame $frame);
    public function visitOutgoingBasicNackFrame(Basic\BasicNackFrame $frame);
    public function visitOutgoingTxSelectFrame(Tx\TxSelectFrame $frame);
    public function visitOutgoingTxCommitFrame(Tx\TxCommitFrame $frame);
    public function visitOutgoingTxRollbackFrame(Tx\TxRollbackFrame $frame);
    public function visitOutgoingConfirmSelectFrame(Confirm\ConfirmSelectFrame $frame);
}
