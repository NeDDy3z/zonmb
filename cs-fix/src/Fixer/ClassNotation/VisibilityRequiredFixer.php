<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
@implements
@phpstan-type
@phpstan-type









*/
final class VisibilityRequiredFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Visibility MUST be declared on all properties and methods; `abstract` and `final` MUST be declared before the visibility; `static` MUST be declared after the visibility.',
[
new CodeSample(
'<?php
class Sample
{
    var $a;
    static protected $var_foo2;

    function A()
    {
    }
}
'
),
new CodeSample(
'<?php
class Sample
{
    const SAMPLE = 1;
}
',
['elements' => ['const']]
),
]
);
}






public function getPriority(): int
{
return 56;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound(Token::getClassyTokenKinds());
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('elements', 'The structural elements to fix (PHP >= 7.1 required for `const`).'))
->setAllowedTypes(['string[]'])
->setAllowedValues([new AllowedValueSubset(['property', 'method', 'const'])])
->setDefault(['property', 'method', 'const'])
->getOption(),
]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$tokensAnalyzer = new TokensAnalyzer($tokens);

$propertyTypeDeclarationKinds = [T_STRING, T_NS_SEPARATOR, CT::T_NULLABLE_TYPE, CT::T_ARRAY_TYPEHINT, CT::T_TYPE_ALTERNATION, CT::T_TYPE_INTERSECTION, CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN, CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE];

if (\defined('T_READONLY')) { 
$propertyReadOnlyType = T_READONLY;
$propertyTypeDeclarationKinds[] = T_READONLY;
} else {
$propertyReadOnlyType = -999;
}

$expectedKindsGeneric = [T_ABSTRACT, T_FINAL, T_PRIVATE, T_PROTECTED, T_PUBLIC, T_STATIC, T_VAR];
$expectedKindsPropertyKinds = [...$expectedKindsGeneric, ...$propertyTypeDeclarationKinds];

foreach (array_reverse($tokensAnalyzer->getClassyElements(), true) as $index => $element) {
if (!\in_array($element['type'], $this->configuration['elements'], true)) {
continue;
}

$abstractFinalIndex = null;
$visibilityIndex = null;
$staticIndex = null;
$typeIndex = null;
$readOnlyIndex = null;
$prevIndex = $tokens->getPrevMeaningfulToken($index);
$expectedKinds = 'property' === $element['type']
? $expectedKindsPropertyKinds
: $expectedKindsGeneric;

while ($tokens[$prevIndex]->isGivenKind($expectedKinds)) {
if ($tokens[$prevIndex]->isGivenKind([T_ABSTRACT, T_FINAL])) {
$abstractFinalIndex = $prevIndex;
} elseif ($tokens[$prevIndex]->isGivenKind(T_STATIC)) {
$staticIndex = $prevIndex;
} elseif ($tokens[$prevIndex]->isGivenKind($propertyReadOnlyType)) {
$readOnlyIndex = $prevIndex;
} elseif ($tokens[$prevIndex]->isGivenKind($propertyTypeDeclarationKinds)) {
$typeIndex = $prevIndex;
} else {
$visibilityIndex = $prevIndex;
}

$prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
}

if (null !== $typeIndex) {
$index = $typeIndex;
}

if ($tokens[$prevIndex]->equals(',')) {
continue;
}

$swapIndex = $staticIndex ?? $readOnlyIndex; 

if (null !== $swapIndex) {
if ($this->isKeywordPlacedProperly($tokens, $swapIndex, $index)) {
$index = $swapIndex;
} else {
$this->moveTokenAndEnsureSingleSpaceFollows($tokens, $swapIndex, $index);
}
}

if (null === $visibilityIndex) {
$tokens->insertAt($index, [new Token([T_PUBLIC, 'public']), new Token([T_WHITESPACE, ' '])]);
} else {
if ($tokens[$visibilityIndex]->isGivenKind(T_VAR)) {
$tokens[$visibilityIndex] = new Token([T_PUBLIC, 'public']);
}
if ($this->isKeywordPlacedProperly($tokens, $visibilityIndex, $index)) {
$index = $visibilityIndex;
} else {
$this->moveTokenAndEnsureSingleSpaceFollows($tokens, $visibilityIndex, $index);
}
}

if (null === $abstractFinalIndex) {
continue;
}

if ($this->isKeywordPlacedProperly($tokens, $abstractFinalIndex, $index)) {
continue;
}

$this->moveTokenAndEnsureSingleSpaceFollows($tokens, $abstractFinalIndex, $index);
}
}

private function isKeywordPlacedProperly(Tokens $tokens, int $keywordIndex, int $comparedIndex): bool
{
return $keywordIndex + 2 === $comparedIndex && ' ' === $tokens[$keywordIndex + 1]->getContent();
}

private function moveTokenAndEnsureSingleSpaceFollows(Tokens $tokens, int $fromIndex, int $toIndex): void
{
$tokens->insertAt($toIndex, [$tokens[$fromIndex], new Token([T_WHITESPACE, ' '])]);
$tokens->clearAt($fromIndex);

if ($tokens[$fromIndex + 1]->isWhitespace()) {
$tokens->clearAt($fromIndex + 1);
}
}
}
