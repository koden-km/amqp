<?php

namespace Recoil\Amqp\v091\Transport;

use Eloquent\Phony\Mock\Proxy\Stubbing\InstanceStubbingProxyInterface;
use Exception;
use PHPUnit_Framework_TestCase;
use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

/**
 * Build a mock transport using Phony.
 */
final class MockTransportBuilder
{
    public function __construct(
        PHPUnit_Framework_TestCase $testCase,
        InstanceStubbingProxyInterface $proxy
    ) {
        $this->testCase = $testCase;
        $this->proxy = $proxy;

        $this->doNothingOnResume();
    }

    /**
     * Configure the transport the receive a frame when it is resumed.
     *
     * @param IncomingFrame $recv The frame that is received.
     */
    public function receiveOnResume(IncomingFrame $recv)
    {
        $this->proxy->resume->does(
            function ($controller) use ($recv) {
                $this->controller = $controller;

                try {
                    $this->controller->onFrame($recv);
                } catch (Exception $e) {
                    $this->controller->onTransportClosed($e);
                }
            }
        );
    }

    /**
     * Configure the transport to close when it is resumed.
     *
     * @param Exception|null $exception The error that caused the closure, if any.
     */
    public function closeOnResume(Exception $exception = null)
    {
        $this->proxy->resume->does(
            function ($controller) use ($exception) {
                $controller->onTransportClosed($exception);
            }
        );
    }

    /**
     * Remove any configured behavior upon resume.
     */
    public function doNothingOnResume()
    {
        $this->proxy->resume->does(
            function ($controller) {
                $this->controller = $controller;
            }
        );
    }

    /**
     * Configure the transport to receive a frame when a specific frame is sent.
     *
     * @param OutgoingFrame|string $send  The outgoing frame to match (exact frame, or class name).
     * @param IncomingFrame        $frame The frame that is received.
     */
    public function receiveOnSend($send, IncomingFrame $recv)
    {
        if (is_string($send)) {
            $send = $this->testCase->isInstanceOf($send);
        }

        $this->proxy->send->with($send)->does(
            function () use ($recv) {
                try {
                    $this->controller->onFrame($recv);
                } catch (Exception $e) {
                    $this->controller->onTransportClosed($e);
                }
            }
        );
    }

    /**
     * Configure the transport to close a frame when a specific frame is sent.
     *
     * @param OutgoingFrame|string $send      The outgoing frame to match (exact frame, or class name).
     * @param Exception|null       $exception The error that caused the closure, if any.
     */
    public function closeOnSend($send, Exception $exception = null)
    {
        if (is_string($send)) {
            $send = $this->testCase->isInstanceOf($send);
        }

        $this->proxy->send->with($send)->does(
            function () use ($exception) {
                $this->controller->onTransportClosed($exception);
            }
        );
    }

    private $testCase;
    private $proxy;
    private $controller;
}
