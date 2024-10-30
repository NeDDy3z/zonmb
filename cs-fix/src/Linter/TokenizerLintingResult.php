<?php

declare(strict_types=1);











namespace PhpCsFixer\Linter;






final class TokenizerLintingResult implements LintingResultInterface
{
private ?\Error $error;

public function __construct(?\Error $error = null)
{
$this->error = $error;
}

public function check(): void
{
if (null !== $this->error) {
throw new LintingException(
\sprintf('%s: %s on line %d.', $this->getMessagePrefix(), $this->error->getMessage(), $this->error->getLine()),
$this->error->getCode(),
$this->error
);
}
}

private function getMessagePrefix(): string
{
return $this->error instanceof \ParseError ? 'Parse error' : 'Fatal error';
}
}
