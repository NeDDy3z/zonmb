<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner;

use PhpCsFixer\Linter\LinterInterface;
use PhpCsFixer\Linter\LintingResultInterface;

/**
@extends




*/
final class LintingFileIterator extends \IteratorIterator implements LintingResultAwareFileIteratorInterface
{



private $currentResult;

private LinterInterface $linter;




public function __construct(\Iterator $iterator, LinterInterface $linter)
{
parent::__construct($iterator);

$this->linter = $linter;
}

public function currentLintingResult(): ?LintingResultInterface
{
return $this->currentResult;
}

public function next(): void
{
parent::next();

$this->currentResult = $this->valid() ? $this->handleItem($this->current()) : null;
}

public function rewind(): void
{
parent::rewind();

$this->currentResult = $this->valid() ? $this->handleItem($this->current()) : null;
}

private function handleItem(\SplFileInfo $file): LintingResultInterface
{
return $this->linter->lintFile($file->getRealPath());
}
}
