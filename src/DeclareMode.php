<?php

namespace Recoil\Amqp;

use Eloquent\Enumeration\AbstractEnumeration;

/**
 * The declare mode defines the behavior of exchange and queue declarations.
 *
 * @see Channel::exchange() to declare an exchange.
 * @see Channel::queue() to declare a queue.
 */
final class DeclareMode extends AbstractEnumeration
{
    /**
     * Create a resource (exchange or queue).
     *
     * In ACTIVE mode, if the resource (an exchange or queue) already exists,
     * and the options specified during declaration match the server the
     * declaration succeeds. If the options do not match the declaration fails.
     *
     * If the resource does not exist it is created and the declaration succeeds.
     */
    const ACTIVE = 'active';

    /**
     * Check if a resource exists (exchange or queue) and its options match.
     *
     * In PASSIVE mode, if the resource (an exchange or queue) already exists,
     * and the options specified during declaration match the server, the
     * declaration succeeds. If the options do not match, or the resource does
     * not exist the declaration fails.
     */
    const PASSIVE = 'passive';
}
