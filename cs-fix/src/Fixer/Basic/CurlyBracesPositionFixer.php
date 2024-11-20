<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Basic;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\Fixer\Indentation;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;

/**
@implements
@phpstan-type
@phpstan-type



















*/
final class CurlyBracesPositionFixer extends AbstractProxyFixer implements ConfigurableFixerInterface, DeprecatedFixerInterface, WhitespacesAwareFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

use Indentation;




public const NEXT_LINE_UNLESS_NEWLINE_AT_SIGNATURE_END = 'next_line_unless_newline_at_signature_end';




public const SAME_LINE = 'same_line';

private BracesPositionFixer $bracesPositionFixer;

public function __construct()
{
$this->bracesPositionFixer = new BracesPositionFixer();

parent::__construct();
}

public function getDefinition(): FixerDefinitionInterface
{
$fixerDefinition = $this->bracesPositionFixer->getDefinition();

return new FixerDefinition(
'Curly braces must be placed as configured.',
$fixerDefinition->getCodeSamples(),
$fixerDefinition->getDescription(),
$fixerDefinition->getRiskyDescription()
);
}







public function getPriority(): int
{
return $this->bracesPositionFixer->getPriority();
}

public function getSuccessorsNames(): array
{
return [
$this->bracesPositionFixer->getName(),
];
}




protected function configurePreNormalisation(array $configuration): void
{
$this->bracesPositionFixer->configure($configuration);
}

protected function createProxyFixers(): array
{
return [
$this->bracesPositionFixer,
];
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return $this->bracesPositionFixer->createConfigurationDefinition();
}
}
