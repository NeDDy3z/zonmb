<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Report\FixReport;






final class ReportSummary
{



private array $changed;

private int $filesCount;

private int $time;

private int $memory;

private bool $addAppliedFixers;

private bool $isDryRun;

private bool $isDecoratedOutput;






public function __construct(
array $changed,
int $filesCount,
int $time,
int $memory,
bool $addAppliedFixers,
bool $isDryRun,
bool $isDecoratedOutput
) {
$this->changed = $changed;
$this->filesCount = $filesCount;
$this->time = $time;
$this->memory = $memory;
$this->addAppliedFixers = $addAppliedFixers;
$this->isDryRun = $isDryRun;
$this->isDecoratedOutput = $isDecoratedOutput;
}

public function isDecoratedOutput(): bool
{
return $this->isDecoratedOutput;
}

public function isDryRun(): bool
{
return $this->isDryRun;
}




public function getChanged(): array
{
return $this->changed;
}

public function getMemory(): int
{
return $this->memory;
}

public function getTime(): int
{
return $this->time;
}

public function getFilesCount(): int
{
return $this->filesCount;
}

public function shouldAddAppliedFixers(): bool
{
return $this->addAppliedFixers;
}
}
