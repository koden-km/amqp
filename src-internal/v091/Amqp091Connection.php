<?php

namespace Recoil\Amqp\v091;

use Recoil\Amqp\Connection;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\v091\Transport\ServerApi;

/**
 * A connection to an AMQP server that uses AMQP v0.9.1.
 *
 * @link http://www.amqp.org/specification/0-9-1/amqp-org-download
 */
final class Amqp091Connection implements Connection
{
    /**
     * @param ServerApi $serverApi The interface used to communicate with the server.
     */
    public function __construct(ServerApi $serverApi)
    {
        $this->serverApi = $serverApi;
        // $this->channels = [];
        // $this->maxChannelId = 0xffff; // $serverApi->capabilities()->maximumChannelCount + 1;
        // $this->nextChannelId = 1;
    }

    /**
     * Create a new AMQP channel.
     *
     * @return Channel             [via promise] The newly created channel.
     * @throws ChannelException    [via promise] If the channel could not be created.
     * @throws ConnectionException [via promise] If not connected to the AMQP server.
     */
    public function channel()
    {
        return $this->serverApi->openChannel()->then(
            function ($channelId) {
                return new Amqp091Channel($this->serverApi, $channelId);
            }
        );
    }

    /**
     * Disconnect from the server.
     */
    public function close()
    {
        $this->serverApi->close();
    }

    private $serverApi;
}
