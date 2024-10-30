<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner\Parallel;

use Fidry\CpuCoreCounter\CpuCoreCounter;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\FinderRegistry;




final class ParallelConfigFactory
{
private static ?CpuCoreCounter $cpuDetector = null;

private function __construct() {}

public static function sequential(): ParallelConfig
{
return new ParallelConfig(1);
}





public static function detect(
?int $filesPerProcess = null,
?int $processTimeout = null
): ParallelConfig {
if (null === self::$cpuDetector) {
self::$cpuDetector = new CpuCoreCounter([
...FinderRegistry::getDefaultLogicalFinders(),
new DummyCpuCoreFinder(1),
]);
}

return new ParallelConfig(
self::$cpuDetector->getCount(),
$filesPerProcess ?? ParallelConfig::DEFAULT_FILES_PER_PROCESS,
$processTimeout ?? ParallelConfig::DEFAULT_PROCESS_TIMEOUT
);
}
}
