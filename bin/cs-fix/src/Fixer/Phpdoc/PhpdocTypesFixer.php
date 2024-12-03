<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractPhpdocTypesFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\AllowedValueSubset;
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
final class PhpdocTypesFixer extends AbstractPhpdocTypesFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;






private const POSSIBLE_TYPES = [
'simple' => [
'array',
'bool',
'callable',
'float',
'int',
'iterable',
'null',
'object',
'string',
],
'alias' => [
'boolean',
'double',
'integer',
],
'meta' => [
'$this',
'false',
'mixed',
'parent',
'resource',
'scalar',
'self',
'static',
'true',
'void',
],
];


private array $typesSetToFix;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'The correct case must be used for standard PHP types in PHPDoc.',
[
new CodeSample(
'<?php
/**
 * @param STRING|String[] $bar
 *
 * @return inT[]
 */
'
),
new CodeSample(
'<?php
/**
 * @param BOOL $foo
 *
 * @return MIXED
 */
',
['groups' => ['simple', 'alias']]
),
]
);
}







public function getPriority(): int
{








return 16;
}

protected function configurePostNormalisation(): void
{
$typesToFix = array_merge(...array_map(static fn (string $group): array => self::POSSIBLE_TYPES[$group], $this->configuration['groups']));

$this->typesSetToFix = array_combine($typesToFix, array_fill(0, \count($typesToFix), true));
}

protected function normalize(string $type): string
{
$typeLower = strtolower($type);
if (isset($this->typesSetToFix[$typeLower])) {
$type = $typeLower;
}



return Preg::replaceCallback(
'/^(\??\s*)([^()[\]{}<>\'"]+)(?<!\s)(\s*[\s()[\]{}<>])/',
fn ($matches) => $matches[1].$this->normalize($matches[2]).$matches[3],
$type
);
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
$possibleGroups = array_keys(self::POSSIBLE_TYPES);

return new FixerConfigurationResolver([
(new FixerOptionBuilder('groups', 'Type groups to fix.'))
->setAllowedTypes(['string[]'])
->setAllowedValues([new AllowedValueSubset($possibleGroups)])
->setDefault($possibleGroups)
->getOption(),
]);
}
}
