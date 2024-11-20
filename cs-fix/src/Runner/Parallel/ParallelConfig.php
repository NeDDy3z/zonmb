<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner\Parallel;




final class ParallelConfig
{

public const DEFAULT_FILES_PER_PROCESS = 10;


public const DEFAULT_PROCESS_TIMEOUT = 120;

private int $filesPerProcess;
private int $maxProcesses;
private int $processTimeout;






public function __construct(
int $maxProcesses = 2,
int $filesPerProcess = self::DEFAULT_FILES_PER_PROCESS,
int $processTimeout = self::DEFAULT_PROCESS_TIMEOUT
) {
if ($maxProcesses <= 0 || $filesPerProcess <= 0 || $processTimeout <= 0) {
throw new \InvalidArgumentException('Invalid parallelisation configuration: only positive integers are allowed');
}

$this->maxProcesses = $maxProcesses;
$this->filesPerProcess = $filesPerProcess;
$this->processTimeout = $processTimeout;
}

public function getFilesPerProcess(): int
{
return $this->filesPerProcess;
}

public function getMaxProcesses(): int
{
return $this->maxProcesses;
}

public function getProcessTimeout(): int
{
return $this->processTimeout;
}
}
