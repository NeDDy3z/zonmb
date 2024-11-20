<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\Runner\Parallel\ParallelConfig;






interface ParallelAwareConfigInterface extends ConfigInterface
{
public function getParallelConfig(): ParallelConfig;

public function setParallelConfig(ParallelConfig $config): ConfigInterface;
}
