<?php

declare(strict_types=1);











namespace PhpCsFixer;






final class ExecutorWithoutErrorHandler
{
private function __construct() {}

/**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     *
     * @throws ExecutorWithoutErrorHandlerException
     */
public static function execute(callable $callback)
{

$error = null;

set_error_handler(static function (int $errorNumber, string $errorString, string $errorFile, int $errorLine) use (&$error): bool {
$error = $errorString;

return true;
});

try {
$result = $callback();
} finally {
restore_error_handler();
}

if (null !== $error) {
throw new ExecutorWithoutErrorHandlerException($error);
}

return $result;
}
}
