<?php

namespace Recoil\Amqp;

use Exception;
use React\Promise\PromiseInterface;
use RuntimeException;

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
        $exception = null;

        $promise->otherwise(
            function ($e) use (&$exception) {
                $exception = $e;
            }
        );

        if ($exception) {
            return $exception;
        }

        throw new RuntimeException('Promise was not rejected.');
    }

    /**
     * Ensure that the given promiser has not been settled.
     *
     * @param PromiseInterface $proiser
     *
     */
    public function assertNotSettled(PromiseInterface $promise)
    {
        $result = null;

        $promise->then(
            function () use (&$result) {
                $result = 'resolved';
            },
            function () use (&$result) {
                $result = 'rejected';
            }
        );

        if ($result) {
            throw new RuntimeException('Promise was unexpectedly ' . $result . '.');
        }
    }
}
