<?php
namespace Recoil\Amqp\Protocol\v091;

use Recoil\Amqp\Protocol\FrameVisitor;

interface OutgoingFrameVisitor extends FrameVisitor
{
    public function visitConnectionStartOkFrame(Connection\StartOkFrame $frame);
    public function visitConnectionSecureOkFrame(Connection\SecureOkFrame $frame);
    public function visitConnectionTuneOkFrame(Connection\TuneOkFrame $frame);
    public function visitConnectionOpenFrame(Connection\OpenFrame $frame);
    public function visitConnectionCloseFrame(Connection\CloseFrame $frame);
    public function visitConnectionCloseOkFrame(Connection\CloseOkFrame $frame);

    public function visitChannelOpenFrame(Channel\OpenFrame $frame);
    public function visitChannelFlowFrame(Channel\FlowFrame $frame);
    public function visitChannelFlowOkFrame(Channel\FlowOkFrame $frame);
    public function visitChannelCloseFrame(Channel\CloseFrame $frame);
    public function visitChannelCloseOkFrame(Channel\CloseOkFrame $frame);

    public function visitAccessRequestFrame(Access\RequestFrame $frame);

    public function visitExchangeDeclareFrame(Exchange\DeclareFrame $frame);
    public function visitExchangeDeleteFrame(Exchange\DeleteFrame $frame);
    public function visitExchangeBindFrame(Exchange\BindFrame $frame);
    public function visitExchangeUnbindFrame(Exchange\UnbindFrame $frame);

    public function visitQueueDeclareFrame(Queue\DeclareFrame $frame);
    public function visitQueueBindFrame(Queue\BindFrame $frame);
    public function visitQueuePurgeFrame(Queue\PurgeFrame $frame);
    public function visitQueueDeleteFrame(Queue\DeleteFrame $frame);
    public function visitQueueUnbindFrame(Queue\UnbindFrame $frame);

    public function visitBasicQosFrame(Basic\QosFrame $frame);
    public function visitBasicConsumeFrame(Basic\ConsumeFrame $frame);
    public function visitBasicCancelFrame(Basic\CancelFrame $frame);
    public function visitBasicPublishFrame(Basic\PublishFrame $frame);
    public function visitBasicGetFrame(Basic\GetFrame $frame);
    public function visitBasicAckFrame(Basic\AckFrame $frame);
    public function visitBasicRejectFrame(Basic\RejectFrame $frame);
    public function visitBasicRecoverAsyncFrame(Basic\RecoverAsyncFrame $frame);
    public function visitBasicRecoverFrame(Basic\RecoverFrame $frame);
    public function visitBasicNackFrame(Basic\NackFrame $frame);

    public function visitTxSelectFrame(Tx\SelectFrame $frame);
    public function visitTxCommitFrame(Tx\CommitFrame $frame);
    public function visitTxRollbackFrame(Tx\RollbackFrame $frame);

    public function visitConfirmSelectFrame(Confirm\SelectFrame $frame);

}
