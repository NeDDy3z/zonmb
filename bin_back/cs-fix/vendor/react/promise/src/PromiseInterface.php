<?php

namespace React\Promise;

/**
@template-covariant
*/
interface PromiseInterface
{
/**
@template
@template


























*/
public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface;

/**
@template
@template











*/
public function catch(callable $onRejected): PromiseInterface;

/**
     * Allows you to execute "cleanup" type tasks in a promise chain.
     *
     * It arranges for `$onFulfilledOrRejected` to be called, with no arguments,
     * when the promise is either fulfilled or rejected.
     *
     * * If `$promise` fulfills, and `$onFulfilledOrRejected` returns successfully,
     *    `$newPromise` will fulfill with the same value as `$promise`.
     * * If `$promise` fulfills, and `$onFulfilledOrRejected` throws or returns a
     *    rejected promise, `$newPromise` will reject with the thrown exception or
     *    rejected promise's reason.
     * * If `$promise` rejects, and `$onFulfilledOrRejected` returns successfully,
     *    `$newPromise` will reject with the same reason as `$promise`.
     * * If `$promise` rejects, and `$onFulfilledOrRejected` throws or returns a
     *    rejected promise, `$newPromise` will reject with the thrown exception or
     *    rejected promise's reason.
     *
     * `finally()` behaves similarly to the synchronous finally statement. When combined
     * with `catch()`, `finally()` allows you to write code that is similar to the familiar
     * synchronous catch/finally pair.
     *
     * Consider the following synchronous code:
     *
     * ```php
     * try {
     *     return doSomething();
     * } catch(\Exception $e) {
     *     return handleError($e);
     * } finally {
     *     cleanup();
     * }
     * ```
     *
     * Similar asynchronous code (with `doSomething()` that returns a promise) can be
     * written:
     *
     * ```php
     * return doSomething()
     *     ->catch('handleError')
     *     ->finally('cleanup');
     * ```
     *
     * @param callable(): (void|PromiseInterface<void>) $onFulfilledOrRejected
     * @return PromiseInterface<T>
     */
public function finally(callable $onFulfilledOrRejected): PromiseInterface;










public function cancel(): void;

/**
@template
@template













*/
public function otherwise(callable $onRejected): PromiseInterface;

/**
     * [Deprecated] Allows you to execute "cleanup" type tasks in a promise chain.
     *
     * This method continues to exist only for BC reasons and to ease upgrading
     * between versions. It is an alias for:
     *
     * ```php
     * $promise->finally($onFulfilledOrRejected);
     * ```
     *
     * @param callable(): (void|PromiseInterface<void>) $onFulfilledOrRejected
     * @return PromiseInterface<T>
     * @deprecated 3.0.0 Use finally() instead
     * @see self::finally()
     */
public function always(callable $onFulfilledOrRejected): PromiseInterface;
}
