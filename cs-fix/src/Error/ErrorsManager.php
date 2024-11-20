<?php

declare(strict_types=1);











namespace PhpCsFixer\Error;








final class ErrorsManager
{



private array $errors = [];






public function getInvalidErrors(): array
{
return array_filter($this->errors, static fn (Error $error): bool => Error::TYPE_INVALID === $error->getType());
}






public function getExceptionErrors(): array
{
return array_filter($this->errors, static fn (Error $error): bool => Error::TYPE_EXCEPTION === $error->getType());
}






public function getLintErrors(): array
{
return array_filter($this->errors, static fn (Error $error): bool => Error::TYPE_LINT === $error->getType());
}






public function forPath(string $path): array
{
return array_values(array_filter($this->errors, static fn (Error $error): bool => $path === $error->getFilePath()));
}




public function isEmpty(): bool
{
return [] === $this->errors;
}

public function report(Error $error): void
{
$this->errors[] = $error;
}
}
