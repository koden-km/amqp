<?php
namespace Recoil\Amqp\Protocol;

interface IncomingFrameVisitor
{
    public function visitConnectionStartFrame(Connection\StartFrame $frame);
    public function visitConnectionSecureFrame(Connection\SecureFrame $frame);
    public function visitConnectionTuneFrame(Connection\TuneFrame $frame);
    public function visitConnectionOpenOkFrame(Connection\OpenOkFrame $frame);
    public function visitConnectionCloseFrame(Connection\CloseFrame $frame);
    public function visitConnectionCloseOkFrame(Connection\CloseOkFrame $frame);
    public function visitConnectionBlockedFrame(Connection\BlockedFrame $frame);
    public function visitConnectionUnblockedFrame(Connection\UnblockedFrame $frame);
    public function visitChannelOpenOkFrame(Channel\OpenOkFrame $frame);
    public function visitChannelFlowFrame(Channel\FlowFrame $frame);
    public function visitChannelFlowOkFrame(Channel\FlowOkFrame $frame);
    public function visitChannelCloseFrame(Channel\CloseFrame $frame);
    public function visitChannelCloseOkFrame(Channel\CloseOkFrame $frame);
    public function visitAccessRequestOkFrame(Access\RequestOkFrame $frame);
    public function visitExchangeDeclareOkFrame(Exchange\DeclareOkFrame $frame);
    public function visitExchangeDeleteOkFrame(Exchange\DeleteOkFrame $frame);
    public function visitExchangeBindOkFrame(Exchange\BindOkFrame $frame);
    public function visitExchangeUnbindOkFrame(Exchange\UnbindOkFrame $frame);
    public function visitQueueDeclareOkFrame(Queue\DeclareOkFrame $frame);
    public function visitQueueBindOkFrame(Queue\BindOkFrame $frame);
    public function visitQueuePurgeOkFrame(Queue\PurgeOkFrame $frame);
    public function visitQueueDeleteOkFrame(Queue\DeleteOkFrame $frame);
    public function visitQueueUnbindOkFrame(Queue\UnbindOkFrame $frame);
    public function visitBasicQosOkFrame(Basic\QosOkFrame $frame);
    public function visitBasicConsumeOkFrame(Basic\ConsumeOkFrame $frame);
    public function visitBasicCancelOkFrame(Basic\CancelOkFrame $frame);
    public function visitBasicReturnFrame(Basic\ReturnFrame $frame);
    public function visitBasicDeliverFrame(Basic\DeliverFrame $frame);
    public function visitBasicGetOkFrame(Basic\GetOkFrame $frame);
    public function visitBasicGetEmptyFrame(Basic\GetEmptyFrame $frame);
    public function visitBasicAckFrame(Basic\AckFrame $frame);
    public function visitBasicRecoverOkFrame(Basic\RecoverOkFrame $frame);
    public function visitBasicNackFrame(Basic\NackFrame $frame);
    public function visitTxSelectOkFrame(Tx\SelectOkFrame $frame);
    public function visitTxCommitOkFrame(Tx\CommitOkFrame $frame);
    public function visitTxRollbackOkFrame(Tx\RollbackOkFrame $frame);
    public function visitConfirmSelectOkFrame(Confirm\SelectOkFrame $frame);
}
