<?php

namespace Recoil\Amqp;

use Exception;
use PHPUnit_Framework_TestCase;
use React\Promise\PromiseInterface;
use RuntimeException;

trait PromiseTestTrait
{
    /**
     * Ensure that the given promise was resolved.
     *
     * @param PromiseInterface $promise
     *
     * @return mixed The resolution value.
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
        try {
            self::join($promise);
        } catch (Exception $e) {
            return $e;
        }

        throw new RuntimeException('Promise was not rejected.');
    }
}
