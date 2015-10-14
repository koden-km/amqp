<?php

namespace Recoil\Amqp\v091;

use Evenement\EventEmitterTrait;
use Exception;
use Recoil\Amqp\Connection;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\v091\Protocol\Channel\ChannelOpenFrame;
use Recoil\Amqp\v091\Protocol\Channel\ChannelOpenOkFrame;
use Recoil\Amqp\v091\Protocol\Transport;
use Recoil\Amqp\v091\Transport\ServerApi;
use RuntimeException;
use function React\Promise\reject;

/**
 * A connection to an AMQP server.
 */
final class Amqp091Connection implements Connection
{
    /**
     * @param Transport $transport           The transport used to communicate with the server.
     * @param integer   $maximumChannelCount The maximum number of channels, as negotiated during the AMQP handshake.
     */
    public function __construct(ServerApi $serverApi)
    {
        $this->serverApi = $serverApi;
        $this->channels = [];
        $this->maxChannelId = 0xffff;
        $this->nextChannelId = 1;
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
        $id = $this->allocateChannelId();

        if (null === $id) {
            return reject(
                // TODO
                new RuntimeException(
                    'Unable to allocate channel ID.'
                )
            );
        }

        $this->serverApi->send(
            ChannelOpenFrame::create($id)
        );

        $this->channels[$id] = new Amqp091Channel($this->serverApi, $id);

        return $this
            ->serverApi
            ->wait(ChannelOpenOkFrame::class, $id)
            ->then(
                function () use ($id) {
                    return $this->channels[$id];
                },
                function (Exception $exception) use ($id) {
                    $this->releaseChannelId($id);

                    throw $exception;
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

    /**
     * @return integer|null
     */
    private function allocateChannelId()
    {
        // first check in range [next, max] ...
        for ($id = $this->nextChannelId; $id <= $this->maxChannelId; ++$id) {
            if (!isset($this->channels[$id])) {
                $this->nextChannelId = $id + 1;

                return $id;
            }
        }

        // then check in range [min, next) ...
        for ($id = 1; $id < $this->nextChannelId; ++$id) {
            if (!isset($this->channels[$id])) {
                $this->nextChannelId = $id + 1;

                return $id;
            }
        }

        // channel IDs are exhausted ...
        return null;
    }

    private function releaseChannelId($id)
    {
        unset($this->channels[$id]);
    }

    use EventEmitterTrait;

    private $transport;
    private $channels;
    private $maxChannelId;
    private $nextChannelId;
}
