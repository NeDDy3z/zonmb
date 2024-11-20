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

/**
@implements
@phpstan-type
@phpstan-type







*/
final class PhpdocScalarFixer extends AbstractPhpdocTypesFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




private const TYPES_MAP = [
'boolean' => 'bool',
'callback' => 'callable',
'double' => 'float',
'integer' => 'int',
'real' => 'float',
'str' => 'string',
];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Scalar types should always be written in the same form. `int` not `integer`, `bool` not `boolean`, `float` not `real` or `double`.',
[
new CodeSample('<?php
/**
 * @param integer $a
 * @param boolean $b
 * @param real $c
 *
 * @return double
 */
function sample($a, $b, $c)
{
    return sample2($a, $b, $c);
}
'),
new CodeSample(
'<?php
/**
 * @param integer $a
 * @param boolean $b
 * @param real $c
 */
function sample($a, $b, $c)
{
    return sample2($a, $b, $c);
}
',
['types' => ['boolean']]
),
]
);
}







public function getPriority(): int
{









return 15;
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
$types = array_keys(self::TYPES_MAP);

return new FixerConfigurationResolver([
(new FixerOptionBuilder('types', 'A list of types to fix.'))
->setAllowedValues([new AllowedValueSubset($types)])
->setDefault($types)
->getOption(),
]);
}

protected function normalize(string $type): string
{
$suffix = '';
while (str_ends_with($type, '[]')) {
$type = substr($type, 0, -2);
$suffix .= '[]';
}

if (\in_array($type, $this->configuration['types'], true)) {
$type = self::TYPES_MAP[$type];
}

return $type.$suffix;
}
}
