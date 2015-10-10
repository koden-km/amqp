<?php
namespace Recoil\Amqp\v091;

use Evenement\EventEmitterTrait;
use Recoil\Amqp\Connection;
use Recoil\Amqp\v091\Protocol\Transport;

/**
 * A connection to an AMQP server.
 */
final class Amqp091Connection implements Connection
{
    public function __construct(Transport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Create a new AMQP channel.
     *
     * Via promise:
     * @return Channel             The newly created channel.
     * @throws ConnectionException if not connected to the AMQP server.
     */
    public function channel()
    {
    }

    /**
     * Disconnect from the server.
     *
     * Via promise:
     * @return null
     */
    public function close()
    {
    }

    use EventEmitterTrait;

    private $transport;
    private $channels;
    private $nextId;
}
