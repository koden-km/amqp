<?php
namespace Recoil\Amqp;

use Eloquent\Enumeration\AbstractEnumeration;

/**
 * The QosScope describes the level at which Quality-of-Service limits are
 * enforced by the server.
 *
 * There are two scopes LOCAL and GLOBAL.
 *
 * Please note that RabbitMQ deviates from the AMQP specification in its
 * treatment of these scopes, see the link below for more information.
 *
 * @link https://www.rabbitmq.com/consumer-prefetch.html
 */
final class QosScope extends AbstractEnumeration
{
    /**
     * Apply the limits across the channel (AMQP-compliant).
     *
     * Apply the limits to each consumer individually (RabbitMQ).
     */
    LOCAL__AMQP_PER_CHANNEL = false;
    LOCAL__RABBITMQ_PER_CONSUMER = false;

    /**
     * Apply the limits across the entire connection (AMQP-compliant).
     *
     * Apply the limits across the channel (RabbitMQ).
     */
    GLOBAL__AMQP_PER_CONNECTION = true;
    GLOBAL__RABBITMQ_PER_CHANNEL = true;
}

