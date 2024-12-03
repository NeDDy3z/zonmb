<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\ConfigurationException\InvalidConfigurationException;
use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;

/**
@implements
@phpstan-type
@phpstan-type










*/
final class PhpdocNoAliasTagFixer extends AbstractProxyFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'No alias PHPDoc tags should be used.',
[
new CodeSample(
'<?php
/**
 * @property string $foo
 * @property-read string $bar
 *
 * @link baz
 */
final class Example
{
}
'
),
new CodeSample(
'<?php
/**
 * @property string $foo
 * @property-read string $bar
 *
 * @link baz
 */
final class Example
{
}
',
['replacements' => ['link' => 'website']]
),
]
);
}







public function getPriority(): int
{
return parent::getPriority();
}

protected function configurePostNormalisation(): void
{

$generalPhpdocTagRenameFixer = $this->proxyFixers['general_phpdoc_tag_rename'];

try {
$generalPhpdocTagRenameFixer->configure([
'fix_annotation' => true,
'fix_inline' => false,
'replacements' => $this->configuration['replacements'],
'case_sensitive' => true,
]);
} catch (InvalidConfigurationException $exception) {
throw new InvalidFixerConfigurationException(
$this->getName(),
Preg::replace('/^\[.+?\] /', '', $exception->getMessage()),
$exception
);
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('replacements', 'Mapping between replaced annotations with new ones.'))
->setAllowedTypes(['array<string, string>'])
->setDefault([
'property-read' => 'property',
'property-write' => 'property',
'type' => 'var',
'link' => 'see',
])
->getOption(),
]);
}

protected function createProxyFixers(): array
{
return [new GeneralPhpdocTagRenameFixer()];
}
}
