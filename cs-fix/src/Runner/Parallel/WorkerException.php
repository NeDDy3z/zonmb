<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner\Parallel;

use Throwable;






final class WorkerException extends \RuntimeException
{
private string $originalTraceAsString;

private function __construct(string $message, int $code)
{
parent::__construct($message, $code);
}











public static function fromRaw(array $data): self
{
$exception = new self(
\sprintf('[%s] %s', $data['class'], $data['message']),
$data['code']
);
$exception->file = $data['file'];
$exception->line = $data['line'];
$exception->originalTraceAsString = \sprintf(
'## %s(%d)%s%s',
$data['file'],
$data['line'],
PHP_EOL,
$data['trace']
);

return $exception;
}

public function getOriginalTraceAsString(): string
{
return $this->originalTraceAsString;
}
}
