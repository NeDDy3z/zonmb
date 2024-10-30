<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Command;






final class FixCommandExitStatusCalculator
{

public const EXIT_STATUS_FLAG_HAS_INVALID_FILES = 4;
public const EXIT_STATUS_FLAG_HAS_CHANGED_FILES = 8;
public const EXIT_STATUS_FLAG_HAS_INVALID_CONFIG = 16;
public const EXIT_STATUS_FLAG_HAS_INVALID_FIXER_CONFIG = 32;
public const EXIT_STATUS_FLAG_EXCEPTION_IN_APP = 64;

public function calculate(
bool $isDryRun,
bool $hasChangedFiles,
bool $hasInvalidErrors,
bool $hasExceptionErrors,
bool $hasLintErrorsAfterFixing
): int {
$exitStatus = 0;

if ($isDryRun) {
if ($hasChangedFiles) {
$exitStatus |= self::EXIT_STATUS_FLAG_HAS_CHANGED_FILES;
}

if ($hasInvalidErrors) {
$exitStatus |= self::EXIT_STATUS_FLAG_HAS_INVALID_FILES;
}
}

if ($hasExceptionErrors || $hasLintErrorsAfterFixing) {
$exitStatus |= self::EXIT_STATUS_FLAG_EXCEPTION_IN_APP;
}

return $exitStatus;
}
}
