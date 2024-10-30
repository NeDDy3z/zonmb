<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerConfiguration;

interface FixerConfigurationResolverInterface
{



public function getOptions(): array;






public function resolve(array $configuration): array;
}
