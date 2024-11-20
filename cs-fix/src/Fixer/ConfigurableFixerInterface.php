<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer;

use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;

/**
@template
@template


*/
interface ConfigurableFixerInterface extends FixerInterface
{
















public function configure(array $configuration): void;




public function getConfigurationDefinition(): FixerConfigurationResolverInterface;
}
