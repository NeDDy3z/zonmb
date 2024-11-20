<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner;

use PhpCsFixer\Linter\LinterInterface;
use PhpCsFixer\Linter\LintingResultInterface;

/**
@extends




*/
final class FileCachingLintingFileIterator extends \CachingIterator implements LintingResultAwareFileIteratorInterface
{
private LinterInterface $linter;
private ?LintingResultInterface $currentResult = null;
private ?LintingResultInterface $nextResult = null;




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

$this->currentResult = $this->nextResult;

if ($this->hasNext()) {
$this->nextResult = $this->handleItem($this->getInnerIterator()->current());
}
}

public function rewind(): void
{
parent::rewind();

if ($this->valid()) {
$this->currentResult = $this->handleItem($this->current());
}

if ($this->hasNext()) {
$this->nextResult = $this->handleItem($this->getInnerIterator()->current());
}
}

private function handleItem(\SplFileInfo $file): LintingResultInterface
{
return $this->linter->lintFile($file->getRealPath());
}
}
