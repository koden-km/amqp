<?php

namespace Recoil\Amqp\v091;

use function React\Promise\reject;
use InvalidArgumentException;
use Recoil\Amqp\Channel;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\Exchange;
use Recoil\Amqp\ExchangeOptions;
use Recoil\Amqp\ExchangeType;
use Recoil\Amqp\Message;
use Recoil\Amqp\PublishOptions;
use Recoil\Amqp\v091\Protocol\Exchange\ExchangeBindFrame;
use Recoil\Amqp\v091\Protocol\Exchange\ExchangeBindOkFrame;
use Recoil\Amqp\v091\Protocol\Exchange\ExchangeDeleteFrame;
use Recoil\Amqp\v091\Protocol\Exchange\ExchangeDeleteOkFrame;
use Recoil\Amqp\v091\Protocol\Exchange\ExchangeUnbindFrame;
use Recoil\Amqp\v091\Protocol\Exchange\ExchangeUnbindOkFrame;
use Recoil\Amqp\v091\Transport\ServerApi;

/**
 * An AMQP exchange.
 *
 * All messages are published to an exchange, and then routed to zero or more
 * queues based on the queue bindings.
 *
 * @see Queue::bind()
 *
 * RabbitMQ also supports exchange-to-exchange bindings.
 */
final class Amqp091Exchange implements Exchange
{
    public function __construct(
        ServerApi $serverApi,
        $name,
        ExchangeType $type,
        ExchangeOptions $options
    ) {
        $this->serverApi = $serverApi;
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * Get the name of the exchange.
     *
     * @return string The exchange name.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the exchange type.
     *
     * @return ExchangeType The exchange type.
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Get the exchange options.
     *
     * @return ExchangeOptions The exchange options.
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * Publish a message to this exchange.
     *
     * @param Message             $message    The message to publish.
     * @param string              $routingKey The routing key for DIRECT and TOPIC exchanges, or empty string for FANOUT and HEADERS exchanges.
     * @param PublishOptions|null $options    Options that affect the publish operation, or null to use the defaults.
     * @param Channel|null        $channel    The channel to use, or null to use an automatically managed channel.
     *
     * @throws ConnectionException      if not connected to the AMQP server.
     * @throws InvalidArgumentException If a routing key is required but not provided.
     */
    public function publish(
        Message $message,
        $routingKey = '',
        PublishOptions $options = null,
        Channel $channel = null
    ) {
        throw new \LogicException('Not implemented.');
    }

    /**
     * Delete this exchange.
     *
     * @return null                [via promise] On success.
     * @throws ConnectionException [via promise] If not connected to the AMQP server.
     * @throws LogicException      [via promise] If the exchange is one of the pre-declared AMQP exchanges.
     */
    public function delete()
    {
        return $this
            ->serverApi
            ->openChannel()
            ->then(
                function ($channelId) use ($source, $routingKey) {
                    $this->serverApi->send(
                        ExchangeDeleteFrame::create(
                            $channelId,
                            null, // reserved
                            $this->name
                        )
                    );

                    return $this->serverApi->wait(ExchangeDeleteOkFrame::class, $channelId);
                }
            )
            ->then(
                function ($frame) {
                    $this->serverApi->closeChannel($frame->frameChannelId);
                }
            );
    }

    /**
     * Bind this exchange to another.
     *
     * @param Exchange $source     The exchange to bind to.
     * @param string   $routingKey The routing key for DIRECT and TOPIC exchanges, or empty string for FANOUT and HEADERS exchanges.
     *
     * @return null                     [via promise] On success.
     * @throws ConnectionException      [via promise] If not connected to the AMQP server.
     * @throws InvalidArgumentException [via promise] If a routing key is required but not provided.
     *
     * Exchange-to-exchange binding is a RabbitMQ specific extension to the AMQP
     * protocol.
     */
    public function bind(Exchange $source, $routingKey = '')
    {
        if ($source->type()->requiresRoutingKey() && '' === $routingKey) {
            return reject(
                new InvalidArgumentException(
                    sprintf(
                        'Bindings on a %s exchange (%s) require a routing key.',
                        $source->type(),
                        $source->name()
                    )
                )
            );
        }

        return $this
            ->serverApi
            ->openChannel()
            ->then(
                function ($channelId) use ($source, $routingKey) {
                    $this->serverApi->send(
                        ExchangeBindFrame::create(
                            $channelId,
                            null, // reserved
                            $this->name,
                            $source->name(),
                            $routingKey
                        )
                    );

                    return $this->serverApi->wait(ExchangeBindOkFrame::class, $channelId);
                }
            )
            ->then(
                function ($frame) {
                    $this->serverApi->closeChannel($frame->frameChannelId);
                }
            );
    }

    /**
     * Unbind this exchange from another.
     *
     * @param Exchange $source     The exchange to unbind from.
     * @param string   $routingKey The routing key for DIRECT and TOPIC exchanges, or empty string for FANOUT and HEADERS exchanges.
     *
     * @return null                     [via promise] On success.
     * @throws ConnectionException      [via promise] If not connected to the AMQP server.
     * @throws InvalidArgumentException [via promise] If a routing key is required but not provided.
     *
     * Exchange-to-exchange binding is a RabbitMQ specific extension
     * to the AMQP protocol.
     */
    public function unbind(Exchange $exchange, $routingKey = '')
    {
        if ($source->type()->requiresRoutingKey() && '' === $routingKey) {
            return reject(
                new InvalidArgumentException(
                    sprintf(
                        'Bindings on a %s exchange (%s) require a routing key.',
                        $source->type(),
                        $source->name()
                    )
                )
            );
        }

        return $this
            ->serverApi
            ->openChannel()
            ->then(
                function ($channelId) use ($source, $routingKey) {
                    $this->serverApi->send(
                        ExchangeUnbindFrame::create(
                            $channelId,
                            null, // reserved
                            $this->name,
                            $source->name(),
                            $routingKey
                        )
                    );

                    return $this->serverApi->wait(ExchangeUnbindOkFrame::class, $channelId);
                }
            )
            ->then(
                function ($frame) {
                    $this->serverApi->closeChannel($frame->frameChannelId);
                }
            );
    }

    private $serverApi;
    private $name;
    private $type;
    private $options;
}
