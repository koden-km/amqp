<?php

namespace Recoil\Amqp;

use Exception;
use React\Promise\PromiseInterface;
use RuntimeException;
use SplObjectStorage;

trait PromiseTestTrait
{
    /**
     * Ensure that the given promise was resolved.
     *
     * @param PromiseInterface $promise
     *
     * @return mixed     The resolution value.
     * @throws Exception If the promise was not resolved.
     */
    public function assertResolved(PromiseInterface $promise)
    {
        $isResolved = false;
        $value = null;
        $exception = null;

        $promise->then(
            function ($v) use (&$value, &$isResolved) {
                $value = $v;
                $isResolved = true;
            },
            function ($e) use (&$exception, &$isResolved) {
                $exception = $e;
            }
        );

        if ($exception) {
            throw $exception;
        } elseif ($isResolved) {
            return $value;
        }

        throw new RuntimeException('Promise was not settled.');
    }

    /**
     * Ensure that the given promise was rejected.
     *
     * @param PromiseInterface $promise
     *
     * @return Exception The rejection exception.
     * @throws Exception If the promise was not rejected.
     */
    public static function assertRejected(PromiseInterface $promise)
    {
        $isRejected = false;
        $exception = null;

        $promise->otherwise(
            function ($e) use (&$isRejected, &$exception) {
                $isRejected = true;
                $exception = $e;
            }
        );

        if ($isRejected) {
            return $exception;
        }

        throw new RuntimeException('Promise was not rejected.');
    }

    /**
     * Ensure that the given promise has not been settled.
     *
     * @param PromiseInterface $promise
     */
    public function assertNotSettled(PromiseInterface $promise)
    {
        $result = null;
        $exception = null;

        $promise->then(
            function () use (&$result) {
                $result = 'Promise was unexpectedly resolved.';
            },
            function ($e) use (&$result, &$exception) {
                $result = 'Promise was unexpectedly rejected.';
                $exception = $e;
            }
        );

        if ($exception) {
            throw $exception;
        }

        if ($result) {
            $this->fail($result);
        }

        // silence risky test warnings
        $this->assertTrue(true);
    }

    public function captureNotifications(PromiseInterface $promise)
    {
        $promise->progress(
            function ($value) use ($promise) {
                if (!$this->notifications) {
                    $this->notifications = new SplObjectStorage();
                }

                if ($this->notifications->contains($promise)) {
                    $values = $this->notifications[$promise];
                    $values[] = $value;
                    $this->notifications[$promise] = $values;
                } else {
                    $this->notifications->attach($promise, [$value]);
                }
            }
        );
    }

    public function notifications(PromiseInterface $promise)
    {
        if (!$this->notifications) {
            return [];
        } elseif (!$this->notifications->contains($promise)) {
            return [];
        }

        return $this->notifications[$promise];
    }

    private $notifications;
}
