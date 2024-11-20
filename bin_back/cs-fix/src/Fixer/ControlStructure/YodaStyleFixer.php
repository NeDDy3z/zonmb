<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
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
final class YodaStyleFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




private $candidatesMap;




private $candidateTypesConfiguration;




private $candidateTypes;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Write conditions in Yoda style (`true`), non-Yoda style (`[\'equal\' => false, \'identical\' => false, \'less_and_greater\' => false]`) or ignore those conditions (`null`) based on configuration.',
[
new CodeSample(
'<?php
    if ($a === null) {
        echo "null";
    }
'
),
new CodeSample(
'<?php
    $b = $c != 1;  // equal
    $a = 1 === $b; // identical
    $c = $c > 3;   // less than
',
[
'equal' => true,
'identical' => false,
'less_and_greater' => null,
]
),
new CodeSample(
'<?php
return $foo === count($bar);
',
[
'always_move_variable' => true,
]
),
new CodeSample(
'<?php
    // Enforce non-Yoda style.
    if (null === $a) {
        echo "null";
    }
',
[
'equal' => false,
'identical' => false,
'less_and_greater' => false,
]
),
]
);
}






public function getPriority(): int
{
return 0;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound($this->candidateTypes);
}

protected function configurePostNormalisation(): void
{
$this->resolveConfiguration();
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$this->fixTokens($tokens);
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('equal', 'Style for equal (`==`, `!=`) statements.'))
->setAllowedTypes(['bool', 'null'])
->setDefault(true)
->getOption(),
(new FixerOptionBuilder('identical', 'Style for identical (`===`, `!==`) statements.'))
->setAllowedTypes(['bool', 'null'])
->setDefault(true)
->getOption(),
(new FixerOptionBuilder('less_and_greater', 'Style for less and greater than (`<`, `<=`, `>`, `>=`) statements.'))
->setAllowedTypes(['bool', 'null'])
->setDefault(null)
->getOption(),
(new FixerOptionBuilder('always_move_variable', 'Whether variables should always be on non assignable side when applying Yoda style.'))
->setAllowedTypes(['bool'])
->setDefault(false)
->getOption(),
]);
}














private function findComparisonEnd(Tokens $tokens, int $index): int
{
++$index;
$count = \count($tokens);

while ($index < $count) {
$token = $tokens[$index];

if ($token->isGivenKind([T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
++$index;

continue;
}

if ($this->isOfLowerPrecedence($token)) {
break;
}

$block = Tokens::detectBlockType($token);

if (null === $block) {
++$index;

continue;
}

if (!$block['isStart']) {
break;
}

$index = $tokens->findBlockEnd($block['type'], $index) + 1;
}

$prev = $tokens->getPrevMeaningfulToken($index);

return $tokens[$prev]->isGivenKind(T_CLOSE_TAG) ? $tokens->getPrevMeaningfulToken($prev) : $prev;
}














private function findComparisonStart(Tokens $tokens, int $index): int
{
--$index;
$nonBlockFound = false;

while (0 <= $index) {
$token = $tokens[$index];

if ($token->isGivenKind([T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
--$index;

continue;
}

if ($token->isGivenKind([CT::T_NAMED_ARGUMENT_COLON])) {
break;
}

if ($this->isOfLowerPrecedence($token)) {
break;
}

$block = Tokens::detectBlockType($token);

if (null === $block) {
--$index;
$nonBlockFound = true;

continue;
}

if (
$block['isStart']
|| ($nonBlockFound && Tokens::BLOCK_TYPE_CURLY_BRACE === $block['type']) 
) {
break;
}

$index = $tokens->findBlockStart($block['type'], $index) - 1;
}

return $tokens->getNextMeaningfulToken($index);
}

private function fixTokens(Tokens $tokens): Tokens
{
for ($i = \count($tokens) - 1; $i > 1; --$i) {
if ($tokens[$i]->isGivenKind($this->candidateTypes)) {
$yoda = $this->candidateTypesConfiguration[$tokens[$i]->getId()];
} elseif (
($tokens[$i]->equals('<') && \in_array('<', $this->candidateTypes, true))
|| ($tokens[$i]->equals('>') && \in_array('>', $this->candidateTypes, true))
) {
$yoda = $this->candidateTypesConfiguration[$tokens[$i]->getContent()];
} else {
continue;
}

$fixableCompareInfo = $this->getCompareFixableInfo($tokens, $i, $yoda);

if (null === $fixableCompareInfo) {
continue;
}

$i = $this->fixTokensCompare(
$tokens,
$fixableCompareInfo['left']['start'],
$fixableCompareInfo['left']['end'],
$i,
$fixableCompareInfo['right']['start'],
$fixableCompareInfo['right']['end']
);
}

return $tokens;
}














private function fixTokensCompare(
Tokens $tokens,
int $startLeft,
int $endLeft,
int $compareOperatorIndex,
int $startRight,
int $endRight
): int {
$type = $tokens[$compareOperatorIndex]->getId();
$content = $tokens[$compareOperatorIndex]->getContent();

if (\array_key_exists($type, $this->candidatesMap)) {
$tokens[$compareOperatorIndex] = clone $this->candidatesMap[$type];
} elseif (\array_key_exists($content, $this->candidatesMap)) {
$tokens[$compareOperatorIndex] = clone $this->candidatesMap[$content];
}

$right = $this->fixTokensComparePart($tokens, $startRight, $endRight);
$left = $this->fixTokensComparePart($tokens, $startLeft, $endLeft);

for ($i = $startRight; $i <= $endRight; ++$i) {
$tokens->clearAt($i);
}

for ($i = $startLeft; $i <= $endLeft; ++$i) {
$tokens->clearAt($i);
}

$tokens->insertAt($startRight, $left);
$tokens->insertAt($startLeft, $right);

return $startLeft;
}

private function fixTokensComparePart(Tokens $tokens, int $start, int $end): Tokens
{
$newTokens = $tokens->generatePartialCode($start, $end);
$newTokens = $this->fixTokens(Tokens::fromCode(\sprintf('<?php %s;', $newTokens)));
$newTokens->clearAt(\count($newTokens) - 1);
$newTokens->clearAt(0);
$newTokens->clearEmptyTokens();

return $newTokens;
}




private function getCompareFixableInfo(Tokens $tokens, int $index, bool $yoda): ?array
{
$right = $this->getRightSideCompareFixableInfo($tokens, $index);

if (!$yoda && $this->isOfLowerPrecedenceAssignment($tokens[$tokens->getNextMeaningfulToken($right['end'])])) {
return null;
}

$left = $this->getLeftSideCompareFixableInfo($tokens, $index);

if ($this->isListStatement($tokens, $left['start'], $left['end']) || $this->isListStatement($tokens, $right['start'], $right['end'])) {
return null; 
}


$strict = $this->configuration['always_move_variable'];
$leftSideIsVariable = $this->isVariable($tokens, $left['start'], $left['end'], $strict);
$rightSideIsVariable = $this->isVariable($tokens, $right['start'], $right['end'], $strict);

if (!($leftSideIsVariable xor $rightSideIsVariable)) {
return null; 
}

if (!$strict) { 
$leftSideIsVariable = $leftSideIsVariable && !$tokens[$left['start']]->equals('(');
$rightSideIsVariable = $rightSideIsVariable && !$tokens[$right['start']]->equals('(');
}

return ($yoda && !$leftSideIsVariable) || (!$yoda && !$rightSideIsVariable)
? null
: ['left' => $left, 'right' => $right];
}




private function getLeftSideCompareFixableInfo(Tokens $tokens, int $index): array
{
return [
'start' => $this->findComparisonStart($tokens, $index),
'end' => $tokens->getPrevMeaningfulToken($index),
];
}




private function getRightSideCompareFixableInfo(Tokens $tokens, int $index): array
{
return [
'start' => $tokens->getNextMeaningfulToken($index),
'end' => $this->findComparisonEnd($tokens, $index),
];
}

private function isListStatement(Tokens $tokens, int $index, int $end): bool
{
for ($i = $index; $i <= $end; ++$i) {
if ($tokens[$i]->isGivenKind([T_LIST, CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN, CT::T_DESTRUCTURING_SQUARE_BRACE_CLOSE])) {
return true;
}
}

return false;
}









private function isOfLowerPrecedence(Token $token): bool
{
static $tokens;

if (null === $tokens) {
$tokens = [
T_BOOLEAN_AND, 
T_BOOLEAN_OR, 
T_CASE, 
T_DOUBLE_ARROW, 
T_ECHO, 
T_GOTO, 
T_LOGICAL_AND, 
T_LOGICAL_OR, 
T_LOGICAL_XOR, 
T_OPEN_TAG, 
T_OPEN_TAG_WITH_ECHO,
T_PRINT, 
T_RETURN, 
T_THROW, 
T_COALESCE,
T_YIELD, 
T_YIELD_FROM,
T_REQUIRE,
T_REQUIRE_ONCE,
T_INCLUDE,
T_INCLUDE_ONCE,
];
}

static $otherTokens = [

'&', '|', '^',

'?', ':',

',', ';',
];

return $this->isOfLowerPrecedenceAssignment($token) || $token->isGivenKind($tokens) || $token->equalsAny($otherTokens);
}





private function isOfLowerPrecedenceAssignment(Token $token): bool
{
static $tokens;

if (null === $tokens) {
$tokens = [
T_AND_EQUAL, 
T_CONCAT_EQUAL, 
T_DIV_EQUAL, 
T_MINUS_EQUAL, 
T_MOD_EQUAL, 
T_MUL_EQUAL, 
T_OR_EQUAL, 
T_PLUS_EQUAL, 
T_POW_EQUAL, 
T_SL_EQUAL, 
T_SR_EQUAL, 
T_XOR_EQUAL, 
T_COALESCE_EQUAL, 
];
}

return $token->equals('=') || $token->isGivenKind($tokens);
}












private function isVariable(Tokens $tokens, int $start, int $end, bool $strict): bool
{
$tokenAnalyzer = new TokensAnalyzer($tokens);

if ($start === $end) {
return $tokens[$start]->isGivenKind(T_VARIABLE);
}

if ($tokens[$start]->equals('(')) {
return true;
}

if ($strict) {
for ($index = $start; $index <= $end; ++$index) {
if (
$tokens[$index]->isCast()
|| $tokens[$index]->isGivenKind(T_INSTANCEOF)
|| $tokens[$index]->equals('!')
|| $tokenAnalyzer->isBinaryOperator($index)
) {
return false;
}
}
}

$index = $start;


while (
$tokens[$index]->equals('(')
&& $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index) === $end
) {
$index = $tokens->getNextMeaningfulToken($index);
$end = $tokens->getPrevMeaningfulToken($end);
}

$expectString = false;

while ($index <= $end) {
$current = $tokens[$index];
if ($current->isComment() || $current->isWhitespace() || $tokens->isEmptyAt($index)) {
++$index;

continue;
}


if ($index === $end) {
return $current->isGivenKind($expectString ? T_STRING : T_VARIABLE);
}

if ($current->isGivenKind([T_LIST, CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN, CT::T_DESTRUCTURING_SQUARE_BRACE_CLOSE])) {
return false;
}

$nextIndex = $tokens->getNextMeaningfulToken($index);
$next = $tokens[$nextIndex];


if ($current->isGivenKind(T_STRING) && $next->isGivenKind(T_DOUBLE_COLON)) {
$index = $tokens->getNextMeaningfulToken($nextIndex);

continue;
}


if ($current->isGivenKind(T_NS_SEPARATOR) && $next->isGivenKind(T_STRING)) {
$index = $nextIndex;

continue;
}


if ($current->isGivenKind(T_STRING) && $next->isGivenKind(T_NS_SEPARATOR)) {
$index = $nextIndex;

continue;
}


if ($current->isGivenKind([T_STRING, T_VARIABLE]) && $next->isObjectOperator()) {
$index = $tokens->getNextMeaningfulToken($nextIndex);
$expectString = true;

continue;
}


if (
$current->isGivenKind($expectString ? T_STRING : T_VARIABLE)
&& $next->equalsAny(['[', [CT::T_ARRAY_INDEX_CURLY_BRACE_OPEN, '{']])
) {
$index = $tokens->findBlockEnd(
$next->equals('[') ? Tokens::BLOCK_TYPE_INDEX_SQUARE_BRACE : Tokens::BLOCK_TYPE_ARRAY_INDEX_CURLY_BRACE,
$nextIndex
);

if ($index === $end) {
return true;
}

$index = $tokens->getNextMeaningfulToken($index);

if (!$tokens[$index]->equalsAny(['[', [CT::T_ARRAY_INDEX_CURLY_BRACE_OPEN, '{']]) && !$tokens[$index]->isObjectOperator()) {
return false;
}

$index = $tokens->getNextMeaningfulToken($index);
$expectString = true;

continue;
}


if ($strict && $current->isGivenKind([T_STRING, T_VARIABLE]) && $next->equals('(')) {
return false;
}


if ($expectString && $current->isGivenKind(CT::T_DYNAMIC_PROP_BRACE_OPEN)) {
$index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_DYNAMIC_PROP_BRACE, $index);
if ($index === $end) {
return true;
}

$index = $tokens->getNextMeaningfulToken($index);

if (!$tokens[$index]->isObjectOperator()) {
return false;
}

$index = $tokens->getNextMeaningfulToken($index);
$expectString = true;

continue;
}

break;
}

return !$this->isConstant($tokens, $start, $end);
}

private function isConstant(Tokens $tokens, int $index, int $end): bool
{
$expectArrayOnly = false;
$expectNumberOnly = false;
$expectNothing = false;

for (; $index <= $end; ++$index) {
$token = $tokens[$index];

if ($token->isComment() || $token->isWhitespace()) {
continue;
}

if ($expectNothing) {
return false;
}

if ($expectArrayOnly) {
if ($token->equalsAny(['(', ')', [CT::T_ARRAY_SQUARE_BRACE_CLOSE]])) {
continue;
}

return false;
}

if ($token->isGivenKind([T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN])) {
$expectArrayOnly = true;

continue;
}

if ($expectNumberOnly && !$token->isGivenKind([T_LNUMBER, T_DNUMBER])) {
return false;
}

if ($token->equals('-')) {
$expectNumberOnly = true;

continue;
}

if (
$token->isGivenKind([T_LNUMBER, T_DNUMBER, T_CONSTANT_ENCAPSED_STRING])
|| $token->equalsAny([[T_STRING, 'true'], [T_STRING, 'false'], [T_STRING, 'null']])
) {
$expectNothing = true;

continue;
}

return false;
}

return true;
}

private function resolveConfiguration(): void
{
$candidateTypes = [];
$this->candidatesMap = [];

if (null !== $this->configuration['equal']) {

$candidateTypes[T_IS_EQUAL] = $this->configuration['equal'];
$candidateTypes[T_IS_NOT_EQUAL] = $this->configuration['equal'];
}

if (null !== $this->configuration['identical']) {

$candidateTypes[T_IS_IDENTICAL] = $this->configuration['identical'];
$candidateTypes[T_IS_NOT_IDENTICAL] = $this->configuration['identical'];
}

if (null !== $this->configuration['less_and_greater']) {

$candidateTypes[T_IS_SMALLER_OR_EQUAL] = $this->configuration['less_and_greater'];
$this->candidatesMap[T_IS_SMALLER_OR_EQUAL] = new Token([T_IS_GREATER_OR_EQUAL, '>=']);

$candidateTypes[T_IS_GREATER_OR_EQUAL] = $this->configuration['less_and_greater'];
$this->candidatesMap[T_IS_GREATER_OR_EQUAL] = new Token([T_IS_SMALLER_OR_EQUAL, '<=']);

$candidateTypes['<'] = $this->configuration['less_and_greater'];
$this->candidatesMap['<'] = new Token('>');

$candidateTypes['>'] = $this->configuration['less_and_greater'];
$this->candidatesMap['>'] = new Token('<');
}

$this->candidateTypesConfiguration = $candidateTypes;
$this->candidateTypes = array_keys($candidateTypes);
}
}
