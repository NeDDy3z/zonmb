<?php

declare(strict_types=1);











namespace PhpCsFixer\Error;




final class SourceExceptionFactory
{



public static function fromArray(array $error): \Throwable
{
$exceptionClass = $error['class'];

try {
$exception = new $exceptionClass($error['message'], $error['code']);

if (
$exception->getMessage() !== $error['message']
|| $exception->getCode() !== $error['code']
) {
throw new \RuntimeException('Failed to create exception from array. Message and code are not the same.');
}
} catch (\Throwable $e) {
$exception = new \RuntimeException(
\sprintf('[%s] %s', $exceptionClass, $error['message']),
$error['code']
);
}

try {
$exceptionReflection = new \ReflectionClass($exception);
foreach (['file', 'line'] as $property) {
$propertyReflection = $exceptionReflection->getProperty($property);
$propertyReflection->setAccessible(true);
$propertyReflection->setValue($exception, $error[$property]);
$propertyReflection->setAccessible(false);
}
} catch (\Throwable $reflectionException) {


}

return $exception;
}
}
