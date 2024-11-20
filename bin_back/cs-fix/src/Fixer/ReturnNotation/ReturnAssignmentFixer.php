<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ReturnNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

final class ReturnAssignmentFixer extends AbstractFixer
{
private TokensAnalyzer $tokensAnalyzer;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Local, dynamic and directly referenced variables should not be assigned and directly returned by a function or method.',
[new CodeSample("<?php\nfunction a() {\n    \$a = 1;\n    return \$a;\n}\n")]
);
}







public function getPriority(): int
{
return -15;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound([T_FUNCTION, T_RETURN, T_VARIABLE]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$tokenCount = \count($tokens);
$this->tokensAnalyzer = new TokensAnalyzer($tokens);

for ($index = 1; $index < $tokenCount; ++$index) {
if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
continue;
}

$next = $tokens->getNextMeaningfulToken($index);
if ($tokens[$next]->isGivenKind(CT::T_RETURN_REF)) {
continue;
}

$functionOpenIndex = $tokens->getNextTokenOfKind($index, ['{', ';']);
if ($tokens[$functionOpenIndex]->equals(';')) { 
$index = $functionOpenIndex - 1;

continue;
}

$functionCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $functionOpenIndex);
$totalTokensAdded = 0;

do {
$tokensAdded = $this->fixFunction(
$tokens,
$index,
$functionOpenIndex,
$functionCloseIndex
);

$functionCloseIndex += $tokensAdded;
$totalTokensAdded += $tokensAdded;
} while ($tokensAdded > 0);

$index = $functionCloseIndex;
$tokenCount += $totalTokensAdded;
}
}








private function fixFunction(Tokens $tokens, int $functionIndex, int $functionOpenIndex, int $functionCloseIndex): int
{
static $riskyKinds = [
CT::T_DYNAMIC_VAR_BRACE_OPEN, 
T_EVAL, 
T_GLOBAL,
T_INCLUDE, 
T_INCLUDE_ONCE, 
T_REQUIRE, 
T_REQUIRE_ONCE, 
];

$inserted = 0;
$candidates = [];
$isRisky = false;

if ($tokens[$tokens->getNextMeaningfulToken($functionIndex)]->isGivenKind(CT::T_RETURN_REF)) {
$isRisky = true;
}



for ($index = $functionIndex + 1; $index < $functionOpenIndex; ++$index) {
if ($tokens[$index]->equals('&')) {
$isRisky = true;

break;
}
}






for ($index = $functionOpenIndex + 1; $index < $functionCloseIndex; ++$index) {
if ($tokens[$index]->isGivenKind(T_FUNCTION)) {
$nestedFunctionOpenIndex = $tokens->getNextTokenOfKind($index, ['{', ';']);
if ($tokens[$nestedFunctionOpenIndex]->equals(';')) { 
$index = $nestedFunctionOpenIndex - 1;

continue;
}

$nestedFunctionCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $nestedFunctionOpenIndex);

$tokensAdded = $this->fixFunction(
$tokens,
$index,
$nestedFunctionOpenIndex,
$nestedFunctionCloseIndex
);

$index = $nestedFunctionCloseIndex + $tokensAdded;
$functionCloseIndex += $tokensAdded;
$inserted += $tokensAdded;
}

if ($isRisky) {
continue; 
}

if ($tokens[$index]->equals('&')) {
$isRisky = true;

continue;
}

if ($tokens[$index]->isGivenKind(T_RETURN)) {
$candidates[] = $index;

continue;
}




if ($tokens[$index]->isGivenKind($riskyKinds)) {
$isRisky = true;

continue;
}

if ($tokens[$index]->isGivenKind(T_STATIC)) {
$nextIndex = $tokens->getNextMeaningfulToken($index);

if (!$tokens[$nextIndex]->isGivenKind(T_FUNCTION)) {
$isRisky = true; 

continue;
}
}

if ($tokens[$index]->equals('$')) {
$nextIndex = $tokens->getNextMeaningfulToken($index);
if ($tokens[$nextIndex]->isGivenKind(T_VARIABLE)) {
$isRisky = true; 

continue;
}
}

if ($this->tokensAnalyzer->isSuperGlobal($index)) {
$isRisky = true;

continue;
}
}

if ($isRisky) {
return $inserted;
}


for ($i = \count($candidates) - 1; $i >= 0; --$i) {
$index = $candidates[$i];


$returnVarIndex = $tokens->getNextMeaningfulToken($index);
if (!$tokens[$returnVarIndex]->isGivenKind(T_VARIABLE)) {
continue; 
}

$endReturnVarIndex = $tokens->getNextMeaningfulToken($returnVarIndex);
if (!$tokens[$endReturnVarIndex]->equalsAny([';', [T_CLOSE_TAG]])) {
continue; 
}


$assignVarEndIndex = $tokens->getPrevMeaningfulToken($index);
if (!$tokens[$assignVarEndIndex]->equals(';')) {
continue; 
}


while (true) {
$prevMeaningFul = $tokens->getPrevMeaningfulToken($assignVarEndIndex);

if (!$tokens[$prevMeaningFul]->equals(')')) {
break;
}

$assignVarEndIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $prevMeaningFul);
}

$assignVarOperatorIndex = $tokens->getPrevTokenOfKind(
$assignVarEndIndex,
['=', ';', '{', '}', [T_OPEN_TAG], [T_OPEN_TAG_WITH_ECHO]]
);

if ($tokens[$assignVarOperatorIndex]->equals('}')) {
$startIndex = $this->isCloseBracePartOfDefinition($tokens, $assignVarOperatorIndex); 

if (null === $startIndex) {
continue;
}

$assignVarOperatorIndex = $tokens->getPrevMeaningfulToken($startIndex);
}

if (!$tokens[$assignVarOperatorIndex]->equals('=')) {
continue;
}


$assignVarIndex = $tokens->getPrevMeaningfulToken($assignVarOperatorIndex);
if (!$tokens[$assignVarIndex]->equals($tokens[$returnVarIndex], false)) {
continue;
}


$beforeAssignVarIndex = $tokens->getPrevMeaningfulToken($assignVarIndex);
if (!$tokens[$beforeAssignVarIndex]->equalsAny([';', '{', '}'])) {
continue;
}


if ($this->isUsedInCatchOrFinally($tokens, $returnVarIndex, $functionOpenIndex, $functionCloseIndex)) {
continue;
}


$inserted += $this->simplifyReturnStatement(
$tokens,
$assignVarIndex,
$assignVarOperatorIndex,
$index,
$endReturnVarIndex
);
}

return $inserted;
}




private function simplifyReturnStatement(
Tokens $tokens,
int $assignVarIndex,
int $assignVarOperatorIndex,
int $returnIndex,
int $returnVarEndIndex
): int {
$inserted = 0;
$originalIndent = $tokens[$assignVarIndex - 1]->isWhitespace()
? $tokens[$assignVarIndex - 1]->getContent()
: null;


if ($tokens[$returnVarEndIndex]->equals(';')) { 
$tokens->clearTokenAndMergeSurroundingWhitespace($returnVarEndIndex);
}

for ($i = $returnIndex; $i <= $returnVarEndIndex - 1; ++$i) {
$this->clearIfSave($tokens, $i);
}


if ($tokens[$returnIndex - 1]->isWhitespace()) {
$content = $tokens[$returnIndex - 1]->getContent();
$fistLinebreakPos = strrpos($content, "\n");
$content = false === $fistLinebreakPos
? ' '
: substr($content, $fistLinebreakPos);

$tokens[$returnIndex - 1] = new Token([T_WHITESPACE, $content]);
}


for ($i = $assignVarIndex; $i <= $assignVarOperatorIndex; ++$i) {
$this->clearIfSave($tokens, $i);
}


$tokens->insertAt($assignVarIndex, new Token([T_RETURN, 'return']));
++$inserted;


if (
null !== $originalIndent
&& $tokens[$assignVarIndex - 1]->isWhitespace()
&& $originalIndent !== $tokens[$assignVarIndex - 1]->getContent()
) {
$tokens[$assignVarIndex - 1] = new Token([T_WHITESPACE, $originalIndent]);
}


$nextIndex = $tokens->getNonEmptySibling($assignVarIndex, 1);
if (!$tokens[$nextIndex]->isWhitespace()) {
$tokens->insertAt($nextIndex, new Token([T_WHITESPACE, ' ']));
++$inserted;
}

return $inserted;
}

private function clearIfSave(Tokens $tokens, int $index): void
{
if ($tokens[$index]->isComment()) {
return;
}

if ($tokens[$index]->isWhitespace() && $tokens[$tokens->getPrevNonWhitespace($index)]->isComment()) {
return;
}

$tokens->clearTokenAndMergeSurroundingWhitespace($index);
}






private function isCloseBracePartOfDefinition(Tokens $tokens, int $index): ?int
{
$index = $tokens->findBlockStart(Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
$candidateIndex = $this->isOpenBraceOfLambda($tokens, $index);

if (null !== $candidateIndex) {
return $candidateIndex;
}

$candidateIndex = $this->isOpenBraceOfAnonymousClass($tokens, $index);

return $candidateIndex ?? $this->isOpenBraceOfMatch($tokens, $index);
}






private function isOpenBraceOfAnonymousClass(Tokens $tokens, int $index): ?int
{
do {
$index = $tokens->getPrevMeaningfulToken($index);
} while ($tokens[$index]->equalsAny([',', [T_STRING], [T_IMPLEMENTS], [T_EXTENDS], [T_NS_SEPARATOR]]));

if ($tokens[$index]->equals(')')) { 
$index = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
$index = $tokens->getPrevMeaningfulToken($index);
}

if (!$tokens[$index]->isGivenKind(T_CLASS) || !$this->tokensAnalyzer->isAnonymousClass($index)) {
return null;
}

return $tokens->getPrevTokenOfKind($index, [[T_NEW]]);
}






private function isOpenBraceOfLambda(Tokens $tokens, int $index): ?int
{
$index = $tokens->getPrevMeaningfulToken($index);

if (!$tokens[$index]->equals(')')) {
return null;
}

$index = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
$index = $tokens->getPrevMeaningfulToken($index);

if ($tokens[$index]->isGivenKind(CT::T_USE_LAMBDA)) {
$index = $tokens->getPrevTokenOfKind($index, [')']);
$index = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
$index = $tokens->getPrevMeaningfulToken($index);
}

if ($tokens[$index]->isGivenKind(CT::T_RETURN_REF)) {
$index = $tokens->getPrevMeaningfulToken($index);
}

if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
return null;
}

$staticCandidate = $tokens->getPrevMeaningfulToken($index);

return $tokens[$staticCandidate]->isGivenKind(T_STATIC) ? $staticCandidate : $index;
}






private function isOpenBraceOfMatch(Tokens $tokens, int $index): ?int
{
if (!\defined('T_MATCH') || !$tokens->isTokenKindFound(T_MATCH)) { 
return null;
}

$index = $tokens->getPrevMeaningfulToken($index);

if (!$tokens[$index]->equals(')')) {
return null;
}

$index = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
$index = $tokens->getPrevMeaningfulToken($index);

return $tokens[$index]->isGivenKind(T_MATCH) ? $index : null;
}

private function isUsedInCatchOrFinally(Tokens $tokens, int $returnVarIndex, int $functionOpenIndex, int $functionCloseIndex): bool
{

$tryIndex = $tokens->getPrevTokenOfKind($returnVarIndex, [[T_TRY]]);
if (null === $tryIndex || $tryIndex <= $functionOpenIndex) {
return false;
}
$tryOpenIndex = $tokens->getNextTokenOfKind($tryIndex, ['{']);
$tryCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $tryOpenIndex);


$nextIndex = $tokens->getNextMeaningfulToken($tryCloseIndex);
if (null === $nextIndex) {
return false;
}


while ($tokens[$nextIndex]->isGivenKind(T_CATCH)) {
$catchOpenIndex = $tokens->getNextTokenOfKind($nextIndex, ['{']);
$catchCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $catchOpenIndex);

if ($catchCloseIndex >= $functionCloseIndex) {
return false;
}
$varIndex = $tokens->getNextTokenOfKind($catchOpenIndex, [$tokens[$returnVarIndex]]);

if (null !== $varIndex && $varIndex < $catchCloseIndex) {
return true;
}

$nextIndex = $tokens->getNextMeaningfulToken($catchCloseIndex);
if (null === $nextIndex) {
return false;
}
}

if (!$tokens[$nextIndex]->isGivenKind(T_FINALLY)) {
return false;
}

$finallyIndex = $nextIndex;
if ($finallyIndex >= $functionCloseIndex) {
return false;
}
$finallyOpenIndex = $tokens->getNextTokenOfKind($finallyIndex, ['{']);
$finallyCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $finallyOpenIndex);
$varIndex = $tokens->getNextTokenOfKind($finallyOpenIndex, [$tokens[$returnVarIndex]]);

if (null !== $varIndex && $varIndex < $finallyCloseIndex) {
return true;
}

return false;
}
}
