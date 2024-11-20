<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\LanguageConstruct;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class IsNullFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Replaces `is_null($var)` expression with `null === $var`.',
[
new CodeSample("<?php\n\$a = is_null(\$b);\n"),
],
null,
'Risky when the function `is_null` is overridden.'
);
}






public function getPriority(): int
{
return 1;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_STRING);
}

public function isRisky(): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
static $sequenceNeeded = [[T_STRING, 'is_null'], '('];
$functionsAnalyzer = new FunctionsAnalyzer();
$currIndex = 0;

while (true) {

$matches = $tokens->findSequence($sequenceNeeded, $currIndex, $tokens->count() - 1, false);


if (null === $matches) {
break;
}


$matches = array_keys($matches);


[$isNullIndex, $currIndex] = $matches;

if (!$functionsAnalyzer->isGlobalFunctionCall($tokens, $matches[0])) {
continue;
}

$next = $tokens->getNextMeaningfulToken($currIndex);

if ($tokens[$next]->equals(')')) {
continue;
}

$prevTokenIndex = $tokens->getPrevMeaningfulToken($matches[0]);


if ($tokens[$prevTokenIndex]->isGivenKind(T_NS_SEPARATOR)) {
$tokens->removeTrailingWhitespace($prevTokenIndex);
$tokens->clearAt($prevTokenIndex);

$prevTokenIndex = $tokens->getPrevMeaningfulToken($prevTokenIndex);
}


$isInvertedNullCheck = false;

if ($tokens[$prevTokenIndex]->equals('!')) {
$isInvertedNullCheck = true;


$tokens->removeTrailingWhitespace($prevTokenIndex);
$tokens->clearAt($prevTokenIndex);
}


$referenceEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $matches[1]);
$isContainingDangerousConstructs = false;

for ($paramTokenIndex = $matches[1]; $paramTokenIndex <= $referenceEnd; ++$paramTokenIndex) {
if (\in_array($tokens[$paramTokenIndex]->getContent(), ['?', '?:', '=', '??'], true)) {
$isContainingDangerousConstructs = true;

break;
}
}


$parentLeftToken = $tokens[$tokens->getPrevMeaningfulToken($isNullIndex)];
$parentRightToken = $tokens[$tokens->getNextMeaningfulToken($referenceEnd)];
$parentOperations = [T_IS_EQUAL, T_IS_NOT_EQUAL, T_IS_IDENTICAL, T_IS_NOT_IDENTICAL];
$wrapIntoParentheses = $parentLeftToken->isCast() || $parentLeftToken->isGivenKind($parentOperations) || $parentRightToken->isGivenKind($parentOperations);


$prevIndex = $tokens->getPrevMeaningfulToken($referenceEnd);

if ($tokens[$prevIndex]->equals(',')) {
$tokens->clearTokenAndMergeSurroundingWhitespace($prevIndex);
}

if (!$isContainingDangerousConstructs) {

$tokens->removeLeadingWhitespace($referenceEnd);
$tokens->clearAt($referenceEnd);


$tokens->removeLeadingWhitespace($matches[1]);
$tokens->removeTrailingWhitespace($matches[1]);
$tokens->clearAt($matches[1]);
}


$replacement = [
new Token([T_STRING, 'null']),
new Token([T_WHITESPACE, ' ']),
new Token($isInvertedNullCheck ? [T_IS_NOT_IDENTICAL, '!=='] : [T_IS_IDENTICAL, '===']),
new Token([T_WHITESPACE, ' ']),
];

if ($wrapIntoParentheses) {
array_unshift($replacement, new Token('('));
$tokens->insertAt($referenceEnd + 1, new Token(')'));
}

$tokens->overrideRange($isNullIndex, $isNullIndex, $replacement);


$currIndex = $isNullIndex;
}
}
}
