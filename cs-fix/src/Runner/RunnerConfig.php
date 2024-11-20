<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner;

use PhpCsFixer\Runner\Parallel\ParallelConfig;






final class RunnerConfig
{
private bool $isDryRun;
private bool $stopOnViolation;
private ParallelConfig $parallelConfig;
private ?string $configFile;

public function __construct(
bool $isDryRun,
bool $stopOnViolation,
ParallelConfig $parallelConfig,
?string $configFile = null
) {
$this->isDryRun = $isDryRun;
$this->stopOnViolation = $stopOnViolation;
$this->parallelConfig = $parallelConfig;
$this->configFile = $configFile;
}

public function isDryRun(): bool
{
return $this->isDryRun;
}

public function shouldStopOnViolation(): bool
{
return $this->stopOnViolation;
}

public function getParallelConfig(): ParallelConfig
{
return $this->parallelConfig;
}

public function getConfigFile(): ?string
{
return $this->configFile;
}
}
