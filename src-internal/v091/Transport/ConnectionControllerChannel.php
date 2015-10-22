<?php

namespace Recoil\Amqp\v091\Transport;

use Exception;
use React\Promise\Deferred;
use Recoil\Amqp\v091\Protocol\IncomingFrame;

/**
 * The connection controllers internal representation of a channel.
 */
final class ConnectionControllerChannel
{
    public function __construct($channelId)
    {
        $this->channelId = $channelId;
    }

    /**
     * Return a promise that is resolved when the channel is opened.
     *
     * @return PromiseInterface
     */
    public function waitForOpen()
    {
        if (null === $this->openDeferred) {
            $this->openDeferred = new Deferred();
        }

        return $this->openDeferred->promise();
    }

    /**
     * Return a promise that is resolved when the channel is closed.
     *
     * @return PromiseInterface
     */
    public function waitForClose()
    {
        if (null === $this->closeDeferred) {
            $this->closeDeferred = new Deferred();
        }

        return $this->closeDeferred->promise();
    }

    /**
     * Wait for the next frame of a given type.
     *
     * @see ServerApi::wait()
     *
     * @param string $type The type of frame (the PHP class name).
     *
     * @return IncomingFrame [via promise] When the next matching frame is received.
     * @throws Exception     [via promise]
     */
    public function waitForFrameType($type)
    {
        $deferred = null;
        $deferred = new Deferred(
            function () use ($type, &$deferred) {
                array_splice(
                    $this->waiters[$type],
                    array_search(
                        $deferred,
                        $this->waiters[$type],
                        true
                    ),
                    1
                );
            }
        );

        $this->waiters[$type][] = $deferred;

        return $deferred->promise();
    }

    /**
     * Receive notification when frames of a given type are received.
     *
     * @see ServerApi::listen()
     *
     * @param string $type The type of frame (the PHP class name).
     *
     * @notify IncomingFrame For each matching frame that is received, unless it
     *                       was matched a "waiter" registered via wait().
     *
     * @return null      [via promise] If the transport or channel is closed cleanly.
     * @throws Exception [via promise]
     */
    public function listenForFrameType($type)
    {
        $deferred = null;
        $deferred = new Deferred(
            function () use ($type, &$deferred) {
                array_splice(
                    $this->listeners[$type],
                    array_search(
                        $deferred,
                        $this->listeners[$type],
                        true
                    ),
                    1
                );
            }
        );

        $this->listeners[$type][] = $deferred;

        return $deferred->promise();
    }

    /**
     * Signal the successful opening of the channel.
     */
    public function onOpen()
    {
        $this->openDeferred->resolve($this->channelId);
        $this->openDeferred = null;
    }

    /**
     * Signal the closure of the channel.
     *
     * @param Exception|null $exception The exception that caused the closure, if any.
     */
    public function onClose(Exception $exception = null)
    {
        if ($this->openDeferred) {
            $this->openDeferred->reject($exception);
        }

        if ($exception) {
            if ($this->closeDeferred) {
                $this->closeDeferred->reject($exception);
            }

            foreach ($this->listeners as $deferreds) {
                foreach ($deferreds as $deferred) {
                    $deferred->reject($exception);
                }
            }
        } else {
            if ($this->closeDeferred) {
                $this->closeDeferred->resolve();
            }

            foreach ($this->listeners as $deferreds) {
                foreach ($deferreds as $deferred) {
                    $deferred->resolve();
                }
            }
        }

        foreach ($this->waiters as $deferreds) {
            foreach ($deferreds as $deferred) {
                $deferred->reject($exception);
            }
        }
    }

    /**
     * Dispatch an incoming frame to the appropriate waiter or listener(s).
     *
     * @param IncomingFrame $frame
     */
    public function dispatch(IncomingFrame $frame)
    {
        $type = get_class($frame);

        if (isset($this->waiters[$type]) && $this->waiters[$type]) {
            $deferred = array_shift($this->waiters[$type]);
            $deferred->resolve($frame);
        } elseif (isset($this->listeners[$type])) {
            foreach ($this->listeners[$type] as $deferred) {
                $deferred->notify($frame);
            }
        }
    }

    private $channelId;
    private $openDeferred;
    private $closeDeferred;
    private $waiters = [];
    private $listeners = [];
}
