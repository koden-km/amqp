<?php
namespace Recoil\Amqp\v091\Protocol;

interface IncomingFrameVisitor
{
    public function visitIncomingHeartbeatFrame(HeartbeatFrame $frame);
    public function visitIncomingConnectionStartFrame(Connection\ConnectionStartFrame $frame);
    public function visitIncomingConnectionSecureFrame(Connection\ConnectionSecureFrame $frame);
    public function visitIncomingConnectionTuneFrame(Connection\ConnectionTuneFrame $frame);
    public function visitIncomingConnectionOpenOkFrame(Connection\ConnectionOpenOkFrame $frame);
    public function visitIncomingConnectionCloseFrame(Connection\ConnectionCloseFrame $frame);
    public function visitIncomingConnectionCloseOkFrame(Connection\ConnectionCloseOkFrame $frame);
    public function visitIncomingConnectionBlockedFrame(Connection\ConnectionBlockedFrame $frame);
    public function visitIncomingConnectionUnblockedFrame(Connection\ConnectionUnblockedFrame $frame);
    public function visitIncomingChannelOpenOkFrame(Channel\ChannelOpenOkFrame $frame);
    public function visitIncomingChannelFlowFrame(Channel\ChannelFlowFrame $frame);
    public function visitIncomingChannelFlowOkFrame(Channel\ChannelFlowOkFrame $frame);
    public function visitIncomingChannelCloseFrame(Channel\ChannelCloseFrame $frame);
    public function visitIncomingChannelCloseOkFrame(Channel\ChannelCloseOkFrame $frame);
    public function visitIncomingAccessRequestOkFrame(Access\AccessRequestOkFrame $frame);
    public function visitIncomingExchangeDeclareOkFrame(Exchange\ExchangeDeclareOkFrame $frame);
    public function visitIncomingExchangeDeleteOkFrame(Exchange\ExchangeDeleteOkFrame $frame);
    public function visitIncomingExchangeBindOkFrame(Exchange\ExchangeBindOkFrame $frame);
    public function visitIncomingExchangeUnbindOkFrame(Exchange\ExchangeUnbindOkFrame $frame);
    public function visitIncomingQueueDeclareOkFrame(Queue\QueueDeclareOkFrame $frame);
    public function visitIncomingQueueBindOkFrame(Queue\QueueBindOkFrame $frame);
    public function visitIncomingQueuePurgeOkFrame(Queue\QueuePurgeOkFrame $frame);
    public function visitIncomingQueueDeleteOkFrame(Queue\QueueDeleteOkFrame $frame);
    public function visitIncomingQueueUnbindOkFrame(Queue\QueueUnbindOkFrame $frame);
    public function visitIncomingBasicQosOkFrame(Basic\BasicQosOkFrame $frame);
    public function visitIncomingBasicConsumeOkFrame(Basic\BasicConsumeOkFrame $frame);
    public function visitIncomingBasicCancelOkFrame(Basic\BasicCancelOkFrame $frame);
    public function visitIncomingBasicReturnFrame(Basic\BasicReturnFrame $frame);
    public function visitIncomingBasicDeliverFrame(Basic\BasicDeliverFrame $frame);
    public function visitIncomingBasicGetOkFrame(Basic\BasicGetOkFrame $frame);
    public function visitIncomingBasicGetEmptyFrame(Basic\BasicGetEmptyFrame $frame);
    public function visitIncomingBasicAckFrame(Basic\BasicAckFrame $frame);
    public function visitIncomingBasicRecoverOkFrame(Basic\BasicRecoverOkFrame $frame);
    public function visitIncomingBasicNackFrame(Basic\BasicNackFrame $frame);
    public function visitIncomingTxSelectOkFrame(Tx\TxSelectOkFrame $frame);
    public function visitIncomingTxCommitOkFrame(Tx\TxCommitOkFrame $frame);
    public function visitIncomingTxRollbackOkFrame(Tx\TxRollbackOkFrame $frame);
    public function visitIncomingConfirmSelectOkFrame(Confirm\ConfirmSelectOkFrame $frame);
}
