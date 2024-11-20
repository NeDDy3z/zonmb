<?php

declare(strict_types=1);











namespace PhpCsFixer\Linter;








final class Linter implements LinterInterface
{
private LinterInterface $subLinter;

public function __construct()
{
$this->subLinter = new TokenizerLinter();
}

public function isAsync(): bool
{
return $this->subLinter->isAsync();
}

public function lintFile(string $path): LintingResultInterface
{
return $this->subLinter->lintFile($path);
}

public function lintSource(string $source): LintingResultInterface
{
return $this->subLinter->lintSource($source);
}
}
