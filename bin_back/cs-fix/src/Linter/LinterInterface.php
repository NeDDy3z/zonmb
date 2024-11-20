<?php

declare(strict_types=1);











namespace PhpCsFixer\Linter;






interface LinterInterface
{
public function isAsync(): bool;




public function lintFile(string $path): LintingResultInterface;




public function lintSource(string $source): LintingResultInterface;
}
