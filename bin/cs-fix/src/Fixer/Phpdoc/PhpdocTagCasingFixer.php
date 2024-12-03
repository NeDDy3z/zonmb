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
final class PhpdocTagCasingFixer extends AbstractProxyFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Fixes casing of PHPDoc tags.',
[
new CodeSample("<?php\n/**\n * @inheritdoc\n */\n"),
new CodeSample("<?php\n/**\n * @inheritdoc\n * @Foo\n */\n", [
'tags' => ['foo'],
]),
]
);
}







public function getPriority(): int
{
return parent::getPriority();
}

protected function configurePostNormalisation(): void
{
$replacements = [];
foreach ($this->configuration['tags'] as $tag) {
$replacements[$tag] = $tag;
}


$generalPhpdocTagRenameFixer = $this->proxyFixers['general_phpdoc_tag_rename'];

try {
$generalPhpdocTagRenameFixer->configure([
'case_sensitive' => false,
'fix_annotation' => true,
'fix_inline' => true,
'replacements' => $replacements,
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
(new FixerOptionBuilder('tags', 'List of tags to fix with their expected casing.'))
->setAllowedTypes(['string[]'])
->setDefault(['inheritDoc'])
->getOption(),
]);
}

protected function createProxyFixers(): array
{
return [new GeneralPhpdocTagRenameFixer()];
}
}
