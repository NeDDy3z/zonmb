<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\CastNotation;

use PhpCsFixer\AbstractFunctionReferenceFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class ModernizeTypesCastingFixer extends AbstractFunctionReferenceFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Replaces `intval`, `floatval`, `doubleval`, `strval` and `boolval` function calls with according type casting operator.',
[
new CodeSample(
'<?php
    $a = intval($b);
    $a = floatval($b);
    $a = doubleval($b);
    $a = strval ($b);
    $a = boolval($b);
'
),
],
null,
'Risky if any of the functions `intval`, `floatval`, `doubleval`, `strval` or `boolval` are overridden.'
);
}






public function getPriority(): int
{
return 31;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{

static $replacement = [
'intval' => [T_INT_CAST, '(int)'],
'floatval' => [T_DOUBLE_CAST, '(float)'],
'doubleval' => [T_DOUBLE_CAST, '(float)'],
'strval' => [T_STRING_CAST, '(string)'],
'boolval' => [T_BOOL_CAST, '(bool)'],
];

$argumentsAnalyzer = new ArgumentsAnalyzer();

foreach ($replacement as $functionIdentity => $newToken) {
$currIndex = 0;

do {

$boundaries = $this->find($functionIdentity, $tokens, $currIndex, $tokens->count() - 1);

if (null === $boundaries) {

continue 2;
}

[$functionName, $openParenthesis, $closeParenthesis] = $boundaries;


$currIndex = $openParenthesis;


if (1 !== $argumentsAnalyzer->countArguments($tokens, $openParenthesis, $closeParenthesis)) {
continue;
}

$paramContentEnd = $closeParenthesis;
$commaCandidate = $tokens->getPrevMeaningfulToken($paramContentEnd);

if ($tokens[$commaCandidate]->equals(',')) {
$tokens->removeTrailingWhitespace($commaCandidate);
$tokens->clearAt($commaCandidate);
$paramContentEnd = $commaCandidate;
}


$countParamTokens = 0;

for ($paramContentIndex = $openParenthesis + 1; $paramContentIndex < $paramContentEnd; ++$paramContentIndex) {

if (!$tokens[$paramContentIndex]->isGivenKind(T_WHITESPACE)) {
++$countParamTokens;
}
}

$preserveParentheses = $countParamTokens > 1;

$afterCloseParenthesisIndex = $tokens->getNextMeaningfulToken($closeParenthesis);
$afterCloseParenthesisToken = $tokens[$afterCloseParenthesisIndex];
$wrapInParentheses = $afterCloseParenthesisToken->equalsAny(['[', '{']) || $afterCloseParenthesisToken->isGivenKind(T_POW);


$prevTokenIndex = $tokens->getPrevMeaningfulToken($functionName);

if ($tokens[$prevTokenIndex]->isGivenKind(T_NS_SEPARATOR)) {

$tokens->removeTrailingWhitespace($prevTokenIndex);
$tokens->clearAt($prevTokenIndex);
}


$replacementSequence = [
new Token($newToken),
new Token([T_WHITESPACE, ' ']),
];

if ($wrapInParentheses) {
array_unshift($replacementSequence, new Token('('));
}

if (!$preserveParentheses) {

$tokens->removeLeadingWhitespace($closeParenthesis);
$tokens->clearAt($closeParenthesis);


$tokens->removeLeadingWhitespace($openParenthesis);
$tokens->removeTrailingWhitespace($openParenthesis);
$tokens->clearAt($openParenthesis);
} else {

$tokens->removeTrailingWhitespace($functionName);
}

if ($wrapInParentheses) {
$tokens->insertAt($closeParenthesis, new Token(')'));
}

$tokens->overrideRange($functionName, $functionName, $replacementSequence);


$currIndex = $functionName;
} while (null !== $currIndex);
}
}
}
