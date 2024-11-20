<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner\Parallel;








final class ProcessIdentifier
{
private const IDENTIFIER_PREFIX = 'php-cs-fixer_parallel_';

private string $identifier;

private function __construct(string $identifier)
{
$this->identifier = $identifier;
}

public function toString(): string
{
return $this->identifier;
}

public static function create(): self
{
return new self(uniqid(self::IDENTIFIER_PREFIX, true));
}

public static function fromRaw(string $identifier): self
{
if (!str_starts_with($identifier, self::IDENTIFIER_PREFIX)) {
throw new ParallelisationException(\sprintf('Invalid process identifier "%s".', $identifier));
}

return new self($identifier);
}
}
