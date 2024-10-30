<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ArrayNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class YieldFromArrayToYieldsFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Yield from array must be unpacked to series of yields.',
[
new CodeSample('<?php function generate() {
    yield from [
        1,
        2,
        3,
    ];
}
'),
],
'The conversion will make the array in `yield from` changed in arrays of 1 less dimension.',
'The rule is risky in case of `yield from` being used multiple times within single function scope, while using list-alike data sources (e.g. `function foo() { yield from ["a"]; yield from ["b"]; }`). It only matters when consuming such iterator with key-value context, because set of yielded keys may be changed after applying this rule.'
);
}

public function isRisky(): bool
{
return true;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_YIELD_FROM);
}







public function getPriority(): int
{
return 0;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{



$inserts = [];

foreach ($this->getYieldsFromToUnpack($tokens) as $index => [$startIndex, $endIndex]) {
$tokens->clearTokenAndMergeSurroundingWhitespace($index);

if ($tokens[$startIndex]->equals('(')) {
$prevStartIndex = $tokens->getPrevMeaningfulToken($startIndex);
$tokens->clearTokenAndMergeSurroundingWhitespace($prevStartIndex); 
}

$tokens->clearTokenAndMergeSurroundingWhitespace($startIndex);
$tokens->clearTokenAndMergeSurroundingWhitespace($endIndex);

$arrayHasTrailingComma = false;

$startIndex = $tokens->getNextMeaningfulToken($startIndex);

$inserts[$startIndex] = [new Token([T_YIELD, 'yield']), new Token([T_WHITESPACE, ' '])];

foreach ($this->findArrayItemCommaIndex(
$tokens,
$startIndex,
$tokens->getPrevMeaningfulToken($endIndex),
) as $commaIndex) {
$nextItemIndex = $tokens->getNextMeaningfulToken($commaIndex);

if ($nextItemIndex < $endIndex) {
$inserts[$nextItemIndex] = [new Token([T_YIELD, 'yield']), new Token([T_WHITESPACE, ' '])];
$tokens[$commaIndex] = new Token(';');
} else {
$arrayHasTrailingComma = true;

$tokens[$commaIndex] = new Token(';');
}
}


if (true === $arrayHasTrailingComma) {
$tokens->clearTokenAndMergeSurroundingWhitespace($tokens->getNextMeaningfulToken($endIndex));
}
}

$tokens->insertSlices($inserts);
}




private function getYieldsFromToUnpack(Tokens $tokens): iterable
{
$tokensCount = $tokens->count();
$index = 0;
while (++$index < $tokensCount) {
if (!$tokens[$index]->isGivenKind(T_YIELD_FROM)) {
continue;
}

$prevIndex = $tokens->getPrevMeaningfulToken($index);
if (!$tokens[$prevIndex]->equalsAny([';', '{', '}', [T_OPEN_TAG]])) {
continue;
}

$arrayStartIndex = $tokens->getNextMeaningfulToken($index);

if (!$tokens[$arrayStartIndex]->isGivenKind([T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN])) {
continue;
}

if ($tokens[$arrayStartIndex]->isGivenKind(T_ARRAY)) {
$startIndex = $tokens->getNextTokenOfKind($arrayStartIndex, ['(']);
$endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $startIndex);
} else {
$startIndex = $arrayStartIndex;
$endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $startIndex);
}


if ($endIndex === $tokens->getNextMeaningfulToken($startIndex)) {
continue;
}


if ([] !== $tokens->findGivenKind(T_YIELD_FROM, $startIndex, $endIndex)) {
continue;
}

yield $index => [$startIndex, $endIndex];
}
}




private function findArrayItemCommaIndex(Tokens $tokens, int $startIndex, int $endIndex): iterable
{
for ($index = $startIndex; $index <= $endIndex; ++$index) {
$token = $tokens[$index];


$blockDefinitionProbe = Tokens::detectBlockType($token);

if (null !== $blockDefinitionProbe && true === $blockDefinitionProbe['isStart']) {
$index = $tokens->findBlockEnd($blockDefinitionProbe['type'], $index);

continue;
}

if (!$tokens[$index]->equals(',')) {
continue;
}

yield $index;
}
}
}
