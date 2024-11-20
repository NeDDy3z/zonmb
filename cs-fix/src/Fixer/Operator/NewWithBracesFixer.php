<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;

/**
@implements
@phpstan-type
@phpstan-type











*/
final class NewWithBracesFixer extends AbstractProxyFixer implements ConfigurableFixerInterface, DeprecatedFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

private NewWithParenthesesFixer $newWithParenthesesFixer;

public function __construct()
{
$this->newWithParenthesesFixer = new NewWithParenthesesFixer();

parent::__construct();
}

public function getDefinition(): FixerDefinitionInterface
{
$fixerDefinition = $this->newWithParenthesesFixer->getDefinition();

return new FixerDefinition(
'All instances created with `new` keyword must (not) be followed by braces.',
$fixerDefinition->getCodeSamples(),
$fixerDefinition->getDescription(),
$fixerDefinition->getRiskyDescription(),
);
}






public function getPriority(): int
{
return $this->newWithParenthesesFixer->getPriority();
}

public function getSuccessorsNames(): array
{
return [
$this->newWithParenthesesFixer->getName(),
];
}




protected function configurePreNormalisation(array $configuration): void
{
$this->newWithParenthesesFixer->configure($configuration);
}

protected function createProxyFixers(): array
{
return [
$this->newWithParenthesesFixer,
];
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return $this->newWithParenthesesFixer->createConfigurationDefinition();
}
}
