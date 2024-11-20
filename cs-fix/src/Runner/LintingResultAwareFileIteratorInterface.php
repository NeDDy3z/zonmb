<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner;

use PhpCsFixer\Linter\LintingResultInterface;

/**
@extends




*/
interface LintingResultAwareFileIteratorInterface extends \Iterator
{
public function currentLintingResult(): ?LintingResultInterface;
}
