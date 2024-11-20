<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\DocBlock;
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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symfony\Component\OptionsResolver\Options;

/**
@implements
@phpstan-type
@phpstan-type








*/
final class PhpdocOrderByValueFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Order PHPDoc tags by value.',
[
new CodeSample(
'<?php
/**
 * @covers Foo
 * @covers Bar
 */
final class MyTest extends \PHPUnit_Framework_TestCase
{}
'
),
new CodeSample(
'<?php
/**
 * @author Bob
 * @author Alice
 */
final class MyTest extends \PHPUnit_Framework_TestCase
{}
',
[
'annotations' => [
'author',
],
]
),
]
);
}







public function getPriority(): int
{
return -10;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound([T_CLASS, T_DOC_COMMENT]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
if ([] === $this->configuration['annotations']) {
return;
}

for ($index = $tokens->count() - 1; $index > 0; --$index) {
foreach ($this->configuration['annotations'] as $type => $typeLowerCase) {
$findPattern = \sprintf(
'/@%s\s.+@%s\s/s',
$type,
$type
);

if (
!$tokens[$index]->isGivenKind(T_DOC_COMMENT)
|| !Preg::match($findPattern, $tokens[$index]->getContent())
) {
continue;
}

$docBlock = new DocBlock($tokens[$index]->getContent());

$annotations = $docBlock->getAnnotationsOfType($type);
$annotationMap = [];

if (\in_array($type, ['property', 'property-read', 'property-write'], true)) {
$replacePattern = \sprintf(
'/(?s)\*\s*@%s\s+(?P<optionalTypes>.+\s+)?\$(?P<comparableContent>\S+).*/',
$type
);

$replacement = '\2';
} elseif ('method' === $type) {
$replacePattern = '/(?s)\*\s*@method\s+(?P<optionalReturnTypes>.+\s+)?(?P<comparableContent>.+)\(.*/';
$replacement = '\2';
} else {
$replacePattern = \sprintf(
'/\*\s*@%s\s+(?P<comparableContent>.+)/',
$typeLowerCase
);

$replacement = '\1';
}

foreach ($annotations as $annotation) {
$rawContent = $annotation->getContent();

$comparableContent = Preg::replace(
$replacePattern,
$replacement,
strtolower(trim($rawContent))
);

$annotationMap[$comparableContent] = $rawContent;
}

$orderedAnnotationMap = $annotationMap;

ksort($orderedAnnotationMap, SORT_STRING);

if ($orderedAnnotationMap === $annotationMap) {
continue;
}

$lines = $docBlock->getLines();

foreach (array_reverse($annotations) as $annotation) {
array_splice(
$lines,
$annotation->getStart(),
$annotation->getEnd() - $annotation->getStart() + 1,
array_pop($orderedAnnotationMap)
);
}

$tokens[$index] = new Token([T_DOC_COMMENT, implode('', $lines)]);
}
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
$allowedValues = [
'author',
'covers',
'coversNothing',
'dataProvider',
'depends',
'group',
'internal',
'method',
'mixin',
'property',
'property-read',
'property-write',
'requires',
'throws',
'uses',
];

return new FixerConfigurationResolver([
(new FixerOptionBuilder('annotations', 'List of annotations to order, e.g. `["covers"]`.'))
->setAllowedTypes(['string[]'])
->setAllowedValues([
new AllowedValueSubset($allowedValues),
])
->setNormalizer(static function (Options $options, array $value): array {
$normalized = [];

foreach ($value as $annotation) {


$normalized[$annotation] = strtolower($annotation);
}

return $normalized;
})
->setDefault([
'covers',
])
->getOption(),
]);
}
}
