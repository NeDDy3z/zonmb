<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\LanguageConstruct;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class NoUnsetOnPropertyFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Properties should be set to `null` instead of using `unset`.',
[new CodeSample("<?php\nunset(\$this->a);\n")],
null,
'Risky when relying on attributes to be removed using `unset` rather than be set to `null`.'.
' Changing variables to `null` instead of unsetting means these still show up when looping over class variables'.
' and reference properties remain unbroken.'.
' With PHP 7.4, this rule might introduce `null` assignments to properties whose type declaration does not allow it.'
);
}

public function isRisky(): bool
{
return true;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_UNSET)
&& $tokens->isAnyTokenKindsFound([T_OBJECT_OPERATOR, T_PAAMAYIM_NEKUDOTAYIM]);
}






public function getPriority(): int
{
return 25;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = $tokens->count() - 1; $index >= 0; --$index) {
if (!$tokens[$index]->isGivenKind(T_UNSET)) {
continue;
}

$unsetsInfo = $this->getUnsetsInfo($tokens, $index);

if (!$this->isAnyUnsetToTransform($unsetsInfo)) {
continue;
}

$isLastUnset = true; 

foreach (array_reverse($unsetsInfo) as $unsetInfo) {
$this->updateTokens($tokens, $unsetInfo, $isLastUnset);
$isLastUnset = false;
}
}
}




private function getUnsetsInfo(Tokens $tokens, int $index): array
{
$argumentsAnalyzer = new ArgumentsAnalyzer();

$unsetStart = $tokens->getNextTokenOfKind($index, ['(']);
$unsetEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $unsetStart);
$isFirst = true;
$unsets = [];

foreach ($argumentsAnalyzer->getArguments($tokens, $unsetStart, $unsetEnd) as $startIndex => $endIndex) {
$startIndex = $tokens->getNextMeaningfulToken($startIndex - 1);
$endIndex = $tokens->getPrevMeaningfulToken($endIndex + 1);
$unsets[] = [
'startIndex' => $startIndex,
'endIndex' => $endIndex,
'isToTransform' => $this->isProperty($tokens, $startIndex, $endIndex),
'isFirst' => $isFirst,
];
$isFirst = false;
}

return $unsets;
}

private function isProperty(Tokens $tokens, int $index, int $endIndex): bool
{
if ($tokens[$index]->isGivenKind(T_VARIABLE)) {
$nextIndex = $tokens->getNextMeaningfulToken($index);

if (null === $nextIndex || !$tokens[$nextIndex]->isGivenKind(T_OBJECT_OPERATOR)) {
return false;
}

$nextIndex = $tokens->getNextMeaningfulToken($nextIndex);
$nextNextIndex = $tokens->getNextMeaningfulToken($nextIndex);

if (null !== $nextNextIndex && $nextNextIndex < $endIndex) {
return false;
}

return null !== $nextIndex && $tokens[$nextIndex]->isGivenKind(T_STRING);
}

if ($tokens[$index]->isGivenKind([T_NS_SEPARATOR, T_STRING])) {
$nextIndex = $tokens->getTokenNotOfKindsSibling($index, 1, [T_DOUBLE_COLON, T_NS_SEPARATOR, T_STRING]);
$nextNextIndex = $tokens->getNextMeaningfulToken($nextIndex);

if (null !== $nextNextIndex && $nextNextIndex < $endIndex) {
return false;
}

return null !== $nextIndex && $tokens[$nextIndex]->isGivenKind(T_VARIABLE);
}

return false;
}




private function isAnyUnsetToTransform(array $unsetsInfo): bool
{
foreach ($unsetsInfo as $unsetInfo) {
if ($unsetInfo['isToTransform']) {
return true;
}
}

return false;
}




private function updateTokens(Tokens $tokens, array $unsetInfo, bool $isLastUnset): void
{

if ($unsetInfo['isFirst'] && $unsetInfo['isToTransform']) {
$braceIndex = $tokens->getPrevTokenOfKind($unsetInfo['startIndex'], ['(']);
$unsetIndex = $tokens->getPrevTokenOfKind($braceIndex, [[T_UNSET]]);
$tokens->clearTokenAndMergeSurroundingWhitespace($braceIndex);
$tokens->clearTokenAndMergeSurroundingWhitespace($unsetIndex);
}


if ($isLastUnset && $unsetInfo['isToTransform']) {
$braceIndex = $tokens->getNextTokenOfKind($unsetInfo['endIndex'], [')']);
$previousIndex = $tokens->getPrevMeaningfulToken($braceIndex);
if ($tokens[$previousIndex]->equals(',')) {
$tokens->clearTokenAndMergeSurroundingWhitespace($previousIndex); 
}

$tokens->clearTokenAndMergeSurroundingWhitespace($braceIndex);
}


if (!$isLastUnset) {
$commaIndex = $tokens->getNextTokenOfKind($unsetInfo['endIndex'], [',']);
$tokens[$commaIndex] = new Token(';');
}


if (!$unsetInfo['isToTransform'] && !$isLastUnset) {
$tokens->insertAt($unsetInfo['endIndex'] + 1, new Token(')'));
}


if (!$unsetInfo['isToTransform'] && !$unsetInfo['isFirst']) {
$tokens->insertAt(
$unsetInfo['startIndex'],
[
new Token([T_UNSET, 'unset']),
new Token('('),
]
);
}



if ($unsetInfo['isToTransform']) {
$tokens->insertAt(
$unsetInfo['endIndex'] + 1,
[
new Token([T_WHITESPACE, ' ']),
new Token('='),
new Token([T_WHITESPACE, ' ']),
new Token([T_STRING, 'null']),
]
);
}
}
}
