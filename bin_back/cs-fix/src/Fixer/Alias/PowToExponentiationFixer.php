<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Alias;

use PhpCsFixer\AbstractFunctionReferenceFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class PowToExponentiationFixer extends AbstractFunctionReferenceFixer
{
public function isCandidate(Tokens $tokens): bool
{

return $tokens->count() > 7 && $tokens->isTokenKindFound(T_STRING);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Converts `pow` to the `**` operator.',
[
new CodeSample(
"<?php\n pow(\$a, 1);\n"
),
],
null,
'Risky when the function `pow` is overridden.'
);
}






public function getPriority(): int
{
return 32;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$candidates = $this->findPowCalls($tokens);
$argumentsAnalyzer = new ArgumentsAnalyzer();
$numberOfTokensAdded = 0;
$previousCloseParenthesisIndex = \count($tokens);

foreach (array_reverse($candidates) as $candidate) {



if ($previousCloseParenthesisIndex < $candidate[2]) {
$previousCloseParenthesisIndex = $candidate[2];
$candidate[2] += $numberOfTokensAdded;
} else {
$previousCloseParenthesisIndex = $candidate[2];
$numberOfTokensAdded = 0;
}

$arguments = $argumentsAnalyzer->getArguments($tokens, $candidate[1], $candidate[2]);

if (2 !== \count($arguments)) {
continue;
}

for ($i = $candidate[1]; $i < $candidate[2]; ++$i) {
if ($tokens[$i]->isGivenKind(T_ELLIPSIS)) {
continue 2;
}
}

$numberOfTokensAdded += $this->fixPowToExponentiation(
$tokens,
$candidate[0], 
$candidate[1], 
$candidate[2], 
$arguments
);
}
}




private function findPowCalls(Tokens $tokens): array
{
$candidates = [];


$end = \count($tokens) - 6;


for ($i = 1; $i < $end; ++$i) {
$candidate = $this->find('pow', $tokens, $i, $end);

if (null === $candidate) {
break;
}

$i = $candidate[1]; 
$candidates[] = $candidate;
}

return $candidates;
}






private function fixPowToExponentiation(Tokens $tokens, int $functionNameIndex, int $openParenthesisIndex, int $closeParenthesisIndex, array $arguments): int
{


$tokens[$tokens->getNextTokenOfKind(reset($arguments), [','])] = new Token([T_POW, '**']);


$tokens->clearAt($closeParenthesisIndex);
$previousIndex = $tokens->getPrevMeaningfulToken($closeParenthesisIndex);

if ($tokens[$previousIndex]->equals(',')) {
$tokens->clearAt($previousIndex); 
}

$added = 0;


foreach (array_reverse($arguments, true) as $argumentStartIndex => $argumentEndIndex) {
if ($this->isParenthesisNeeded($tokens, $argumentStartIndex, $argumentEndIndex)) {
$tokens->insertAt($argumentEndIndex + 1, new Token(')'));
$tokens->insertAt($argumentStartIndex, new Token('('));
$added += 2;
}
}


$tokens->clearAt($openParenthesisIndex);
$tokens->clearAt($functionNameIndex);

$prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($functionNameIndex);

if ($tokens[$prevMeaningfulTokenIndex]->isGivenKind(T_NS_SEPARATOR)) {
$tokens->clearAt($prevMeaningfulTokenIndex);
}

return $added;
}

private function isParenthesisNeeded(Tokens $tokens, int $argumentStartIndex, int $argumentEndIndex): bool
{
static $allowedKinds = null;

if (null === $allowedKinds) {
$allowedKinds = $this->getAllowedKinds();
}

for ($i = $argumentStartIndex; $i <= $argumentEndIndex; ++$i) {
if ($tokens[$i]->isGivenKind($allowedKinds) || $tokens->isEmptyAt($i)) {
continue;
}

$blockType = Tokens::detectBlockType($tokens[$i]);

if (null !== $blockType) {
$i = $tokens->findBlockEnd($blockType['type'], $i);

continue;
}

if ($tokens[$i]->equals('$')) {
$i = $tokens->getNextMeaningfulToken($i);
if ($tokens[$i]->isGivenKind(CT::T_DYNAMIC_VAR_BRACE_OPEN)) {
$i = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_DYNAMIC_VAR_BRACE, $i);

continue;
}
}

if ($tokens[$i]->equals('+') && $tokens->getPrevMeaningfulToken($i) < $argumentStartIndex) {
continue;
}

return true;
}

return false;
}




private function getAllowedKinds(): array
{
return [
T_DNUMBER, T_LNUMBER, T_VARIABLE, T_STRING, T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_CAST,
T_INT_CAST, T_INC, T_DEC, T_NS_SEPARATOR, T_WHITESPACE, T_DOUBLE_COLON, T_LINE, T_COMMENT, T_DOC_COMMENT,
CT::T_NAMESPACE_OPERATOR,
...Token::getObjectOperatorKinds(),
];
}
}
