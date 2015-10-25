<?php

namespace Recoil\Amqp\v091;

use Recoil\Amqp\Channel;
use Recoil\Amqp\DeclareMode;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\Exception\DeclareException;
use Recoil\Amqp\Exception\ResourceLockedException;
use Recoil\Amqp\ExchangeOptions;
use Recoil\Amqp\ExchangeType;
use Recoil\Amqp\QosScope;
use Recoil\Amqp\QueueOptions;
use Recoil\Amqp\v091\Protocol\Exchange\ExchangeDeclareFrame;
use Recoil\Amqp\v091\Protocol\Exchange\ExchangeDeclareOkFrame;
use Recoil\Amqp\v091\Protocol\Queue\QueueDeclareFrame;
use Recoil\Amqp\v091\Protocol\Queue\QueueDeclareOkFrame;
use Recoil\Amqp\v091\Transport\ServerApi;

/**
 * An AMQP channel.
 */
final class Amqp091Channel implements Channel
{
    public function __construct(ServerApi $serverApi, $channelId)
    {
        $this->serverApi = $serverApi;
        $this->channelId = $channelId;
    }

    /**
     * Get the channel ID.
     *
     * @return integer The channel ID.
     */
    public function id()
    {
        return $this->channelId;
    }

    /**
     * Declare an exchange.
     *
     * This method declares a new exchange on the server. To get one of the
     * pre-declared exchanges, use the one of the following methods:
     *
     * @see Channel::directExchange() to use the nameless, direct exchange, which is used to route messages to specific queues, by name.
     * @see Channel::amqExchange() to use one of the "amq.<type>" exchanges, which are the pre-declared exchanges for each of the exchange types.
     *
     * Exchange names beginning with "amq." are reserved and will not be created,
     * however, this method can be used to access those exchanges if they
     * already exist.
     *
     * @param string               $name    The exchange name.
     * @param ExchangeType         $type    The exchange type.
     * @param ExchangeOptions|null $options Options that affect the behaviour of the exchange, or null to use the defaults.
     * @param DeclareMode|null     $mode    The declare mode, ACTIVE (create the exchange, the default) or PASSIVE (check if the exchange exists).
     *
     * Via a promise:
     * @return Exchange            [via promise] The exchange.
     * @throws DeclareException    [via promise] if the exchange could not be declared because it already exists with different options.
     * @throws ConnectionException [via promise] if not connected to the AMQP server.
     * @throws ChannelException    [via promise] if the channel has been closed.
     */
    public function exchange(
        $name,
        ExchangeType $type,
        ExchangeOptions $options = null,
        DeclareMode $mode = null
    ) {
        if (null === $options) {
            $options = ExchangeOptions::defaults();
        }

        $this->serverApi->send(
            ExchangeDeclareFrame::create(
                $this->channelId,
                null, // reserved
                $name,
                $type->value(),
                DeclareMode::PASSIVE() === $mode,
                $options->durable,
                $options->autoDelete,
                $options->internal
            )
        );

        // @todo Throw appropriate exceptions

        return $this->serverApi->wait(ExchangeDeclareOkFrame::class, $this->channelId)->then(
            function ($frame) use ($name, $type, $options) {
                return new Amqp091Exchange(
                    $this->serverApi,
                    $name,
                    $type,
                    $options
                );
            }
        );
    }

    /**
     * Get the pre-declared, nameless, direct exchange.
     *
     * This exchange is used to route messages to specific queues by name.
     *
     * Every queue is automatically bound to the nameless exchange with a
     * routing key the same as the queue name.
     *
     * @see https://www.rabbitmq.com/tutorials/amqp-concepts.html#exchanges
     *
     * @see Channel::exchange() to declare a new exchange.
     * @see Channel::amqExchange() to use one of the "amq.<type>" exchanges, which are the pre-declared exchanges for each of the exchange types.
     *
     * @return Exchange            [via promise] The exchange.
     * @throws ConnectionException [via promise] If not connected to the AMQP server.
     * @throws ChannelException    [via promise] If the channel has been closed.
     */
    public function directExchange()
    {
        // @todo Throw appropriate exceptions
        return resolve(
            new Amqp091Exchange(
                $this->serverApi,
                '',
                ExchangeType::DIRECT(),
                ExchangeOptions::none()->durable(true)
            )
        );
    }

    /**
     * Get the pre-declared "amq.<type>" exchange of the given type.
     *
     * @see https://www.rabbitmq.com/tutorials/amqp-concepts.html#exchanges
     *
     * @see Channel::exchange() to declare a new exchange.
     * @see Channel::directExchange() to use the nameless, direct exchange, which is used to route messages to specific queues, by name.
     *
     * @param ExchangeType $type The exchange type.
     *
     * @return Exchange            [via promise] The exchange.
     * @throws ConnectionException [via promise] If not connected to the AMQP server.
     * @throws ChannelException    [via promise] If the channel has been closed.
     */
    public function amqExchange(ExchangeType $type)
    {
        // @todo Throw appropriate exceptions
        return resolve(
            new Amqp091Exchange(
                $this->serverApi,
                'amq.' . $type->value(),
                $type,
                ExchangeOptions::none()->durable(true)
            )
        );
    }

    /**
     * Declare a queue.
     *
     * Called with no arguments, this method will return an exclusive,
     * auto-deleting queue with a server-generated name.
     *
     * @param string            $name    The queue name, or an empty string to use a random, unique name.
     * @param QueueOptions|null $options Options that affect the behaviour of the queue, or null to use the defaults.
     * @param DeclareMode|null  $mode    The declare mode, ACTIVE (create the queue, the default) or PASSIVE (check if the queue exists).
     *
     * @return Queue                   [via promise] The queue.
     * @throws DeclareException        [via promise] If the queue could not be declared because it already exists with different options.
     * @throws ResourceLockedException [via promise] If the queue already exists, but another connection has exclusive access.
     * @throws ConnectionException     [via promise] If not connected to the AMQP server.
     * @throws ChannelException        [via promise] If the channel has been closed.
     */
    public function queue(
        $name = '',
        QueueOptions $options = null,
        DeclareMode $mode = null
    ) {
        if (null === $options) {
            $options = QueueOptions::defaults();
        }

        $this->serverApi->send(
            QueueDeclareFrame::create(
                $this->channelId,
                null, // reserved
                $name,
                DeclareMode::PASSIVE() === $mode,
                $options->durable,
                $options->exclusive,
                $options->autoDelete
            )
        );

        // @todo Throw appropriate exceptions

        return $this->serverApi->wait(QueueDeclareOkFrame::class, $this->channelId)->then(
            function ($frame) use ($options) {
                return new Amqp091Queue(
                    $this->serverApi,
                    $frame->queue,
                    $options
                );
            }
        );
    }

    /**
     * Set the channel's Quality-of-Service limits.
     *
     * @param integer|null $count The maximum number of un-acknowledged messages to accept, or null to use the server default.
     * @param integer|null $size  The maximum total size of un-acknowledged messages to accept, in bytes, or null to use the server default.
     * @param QosScope     $scope The scope at which the change is applied.
     *
     * @return null                [via promise] On success.
     * @throws ConnectionException [via promise] If not connected to the AMQP server.
     * @throws ChannelException    [via promise] If the channel has been closed.
     *
     * Please note that RabbitMQ does not currently (as of v3.5.5) support
     * prefetch-size limits.
     */
    public function qos($count, $size = null, QosScope $scope = null)
    {
        throw new \LogicException('Not implemented.');
    }

    /**
     * Close the channel.
     */
    public function close()
    {
        $this->serverApi->send(
            ChannelCloseFrame::create($this->channelId)
        );

        $this->serverApi = null;
    }

    private $serverApi;
    private $channelId;
}
