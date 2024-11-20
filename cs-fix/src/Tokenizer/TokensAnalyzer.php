<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer;

use PhpCsFixer\Tokenizer\Analyzer\AttributeAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\GotoLabelAnalyzer;

/**
@phpstan-type









*/
final class TokensAnalyzer
{



private Tokens $tokens;

private ?GotoLabelAnalyzer $gotoLabelAnalyzer = null;

public function __construct(Tokens $tokens)
{
$this->tokens = $tokens;
}






public function getClassyElements(): array
{
$elements = [];

for ($index = 1, $count = \count($this->tokens) - 2; $index < $count; ++$index) {
if ($this->tokens[$index]->isClassy()) {
[$index, $newElements] = $this->findClassyElements($index, $index);
$elements += $newElements;
}
}

ksort($elements);

return $elements;
}










public function getClassyModifiers(int $index): array
{
if (!$this->tokens[$index]->isClassy()) {
throw new \InvalidArgumentException(\sprintf('Not an "classy" at given index %d.', $index));
}

$readOnlyPossible = \defined('T_READONLY'); 
$modifiers = ['final' => null, 'abstract' => null, 'readonly' => null];

while (true) {
$index = $this->tokens->getPrevMeaningfulToken($index);

if ($this->tokens[$index]->isGivenKind(T_FINAL)) {
$modifiers['final'] = $index;
} elseif ($this->tokens[$index]->isGivenKind(T_ABSTRACT)) {
$modifiers['abstract'] = $index;
} elseif ($readOnlyPossible && $this->tokens[$index]->isGivenKind(T_READONLY)) {
$modifiers['readonly'] = $index;
} else { 
break;
}
}

return $modifiers;
}








public function getImportUseIndexes(bool $perNamespace = false): array
{
$tokens = $this->tokens;

$uses = [];
$namespaceIndex = 0;

for ($index = 0, $limit = $tokens->count(); $index < $limit; ++$index) {
$token = $tokens[$index];

if ($token->isGivenKind(T_NAMESPACE)) {
$nextTokenIndex = $tokens->getNextTokenOfKind($index, [';', '{']);
$nextToken = $tokens[$nextTokenIndex];

if ($nextToken->equals('{')) {
$index = $nextTokenIndex;
}

if ($perNamespace) {
++$namespaceIndex;
}

continue;
}

if ($token->isGivenKind(T_USE)) {
$uses[$namespaceIndex][] = $index;
}
}

if (!$perNamespace && isset($uses[$namespaceIndex])) {
return $uses[$namespaceIndex];
}

return $uses;
}




public function isArray(int $index): bool
{
return $this->tokens[$index]->isGivenKind([T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN]);
}






public function isArrayMultiLine(int $index): bool
{
if (!$this->isArray($index)) {
throw new \InvalidArgumentException(\sprintf('Not an array at given index %d.', $index));
}

$tokens = $this->tokens;



if ($tokens[$index]->isGivenKind(T_ARRAY)) {
$index = $tokens->getNextMeaningfulToken($index);
}

return $this->isBlockMultiline($tokens, $index);
}

public function isBlockMultiline(Tokens $tokens, int $index): bool
{
$blockType = Tokens::detectBlockType($tokens[$index]);

if (null === $blockType || !$blockType['isStart']) {
throw new \InvalidArgumentException(\sprintf('Not an block start at given index %d.', $index));
}

$endIndex = $tokens->findBlockEnd($blockType['type'], $index);

for (++$index; $index < $endIndex; ++$index) {
$token = $tokens[$index];
$blockType = Tokens::detectBlockType($token);

if (null !== $blockType && $blockType['isStart']) {
$index = $tokens->findBlockEnd($blockType['type'], $index);

continue;
}

if (
$token->isWhitespace()
&& !$tokens[$index - 1]->isGivenKind(T_END_HEREDOC)
&& str_contains($token->getContent(), "\n")
) {
return true;
}
}

return false;
}






public function getMethodAttributes(int $index): array
{
if (!$this->tokens[$index]->isGivenKind(T_FUNCTION)) {
throw new \LogicException(\sprintf('No T_FUNCTION at given index %d, got "%s".', $index, $this->tokens[$index]->getName()));
}

$attributes = [
'visibility' => null,
'static' => false,
'abstract' => false,
'final' => false,
];

for ($i = $index; $i >= 0; --$i) {
$i = $this->tokens->getPrevMeaningfulToken($i);
$token = $this->tokens[$i];

if ($token->isGivenKind(T_STATIC)) {
$attributes['static'] = true;

continue;
}

if ($token->isGivenKind(T_FINAL)) {
$attributes['final'] = true;

continue;
}

if ($token->isGivenKind(T_ABSTRACT)) {
$attributes['abstract'] = true;

continue;
}



if ($token->isGivenKind(T_PRIVATE)) {
$attributes['visibility'] = T_PRIVATE;

continue;
}

if ($token->isGivenKind(T_PROTECTED)) {
$attributes['visibility'] = T_PROTECTED;

continue;
}

if ($token->isGivenKind(T_PUBLIC)) {
$attributes['visibility'] = T_PUBLIC;

continue;
}



break;
}

return $attributes;
}




public function isAnonymousClass(int $index): bool
{
if (!$this->tokens[$index]->isClassy()) {
throw new \LogicException(\sprintf('No classy token at given index %d.', $index));
}

if (!$this->tokens[$index]->isGivenKind(T_CLASS)) {
return false;
}

$index = $this->tokens->getPrevMeaningfulToken($index);

if (\defined('T_READONLY') && $this->tokens[$index]->isGivenKind(T_READONLY)) { 
$index = $this->tokens->getPrevMeaningfulToken($index);
}

while ($this->tokens[$index]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
$index = $this->tokens->findBlockStart(Tokens::BLOCK_TYPE_ATTRIBUTE, $index);
$index = $this->tokens->getPrevMeaningfulToken($index);
}

return $this->tokens[$index]->isGivenKind(T_NEW);
}




public function isLambda(int $index): bool
{
if (!$this->tokens[$index]->isGivenKind([T_FUNCTION, T_FN])) {
throw new \LogicException(\sprintf('No T_FUNCTION or T_FN at given index %d, got "%s".', $index, $this->tokens[$index]->getName()));
}

$startParenthesisIndex = $this->tokens->getNextMeaningfulToken($index);
$startParenthesisToken = $this->tokens[$startParenthesisIndex];


if ($startParenthesisToken->isGivenKind(CT::T_RETURN_REF)) {
$startParenthesisIndex = $this->tokens->getNextMeaningfulToken($startParenthesisIndex);
$startParenthesisToken = $this->tokens[$startParenthesisIndex];
}

return $startParenthesisToken->equals('(');
}

public function getLastTokenIndexOfArrowFunction(int $index): int
{
if (!$this->tokens[$index]->isGivenKind(T_FN)) {
throw new \InvalidArgumentException(\sprintf('Not an "arrow function" at given index %d.', $index));
}

$stopTokens = [')', ']', ',', ';', [T_CLOSE_TAG]];
$index = $this->tokens->getNextTokenOfKind($index, [[T_DOUBLE_ARROW]]);

while (true) {
$index = $this->tokens->getNextMeaningfulToken($index);

if ($this->tokens[$index]->equalsAny($stopTokens)) {
break;
}

$blockType = Tokens::detectBlockType($this->tokens[$index]);

if (null === $blockType) {
continue;
}

if ($blockType['isStart']) {
$index = $this->tokens->findBlockEnd($blockType['type'], $index);

continue;
}

break;
}

return $this->tokens->getPrevMeaningfulToken($index);
}




public function isConstantInvocation(int $index): bool
{
if (!$this->tokens[$index]->isGivenKind(T_STRING)) {
throw new \LogicException(\sprintf('No T_STRING at given index %d, got "%s".', $index, $this->tokens[$index]->getName()));
}

$nextIndex = $this->tokens->getNextMeaningfulToken($index);

if (
$this->tokens[$nextIndex]->equalsAny(['(', '{'])
|| $this->tokens[$nextIndex]->isGivenKind([T_AS, T_DOUBLE_COLON, T_ELLIPSIS, T_NS_SEPARATOR, CT::T_RETURN_REF, CT::T_TYPE_ALTERNATION, CT::T_TYPE_INTERSECTION, T_VARIABLE])
) {
return false;
}

$prevIndex = $this->tokens->getPrevMeaningfulToken($index);

if ($this->tokens[$prevIndex]->isGivenKind([T_AS, T_CLASS, T_CONST, T_DOUBLE_COLON, T_FUNCTION, T_GOTO, CT::T_GROUP_IMPORT_BRACE_OPEN, T_INTERFACE, T_TRAIT, CT::T_TYPE_COLON, CT::T_TYPE_ALTERNATION, CT::T_TYPE_INTERSECTION]) || $this->tokens[$prevIndex]->isObjectOperator()) {
return false;
}

while ($this->tokens[$prevIndex]->isGivenKind([CT::T_NAMESPACE_OPERATOR, T_NS_SEPARATOR, T_STRING, CT::T_ARRAY_TYPEHINT])) {
$prevIndex = $this->tokens->getPrevMeaningfulToken($prevIndex);
}

if ($this->tokens[$prevIndex]->isGivenKind([CT::T_CONST_IMPORT, T_EXTENDS, CT::T_FUNCTION_IMPORT, T_IMPLEMENTS, T_INSTANCEOF, T_INSTEADOF, T_NAMESPACE, T_NEW, CT::T_NULLABLE_TYPE, CT::T_TYPE_COLON, T_USE, CT::T_USE_TRAIT, CT::T_TYPE_INTERSECTION, CT::T_TYPE_ALTERNATION, T_CONST, CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE])) {
return false;
}




if ($this->tokens[$nextIndex]->equals('&') && $this->tokens[$this->tokens->getNextMeaningfulToken($nextIndex)]->isGivenKind(T_VARIABLE)) {
$checkIndex = $this->tokens->getPrevTokenOfKind($prevIndex, [';', '{', '}', [T_FUNCTION], [T_OPEN_TAG], [T_OPEN_TAG_WITH_ECHO]]);

if ($this->tokens[$checkIndex]->isGivenKind(T_FUNCTION)) {
return false;
}
}


if ($this->tokens[$prevIndex]->equals(',')) {
$checkIndex = $prevIndex;

while ($this->tokens[$checkIndex]->equalsAny([',', [T_AS], [CT::T_NAMESPACE_OPERATOR], [T_NS_SEPARATOR], [T_STRING]])) {
$checkIndex = $this->tokens->getPrevMeaningfulToken($checkIndex);
}

if ($this->tokens[$checkIndex]->isGivenKind([T_EXTENDS, CT::T_GROUP_IMPORT_BRACE_OPEN, T_IMPLEMENTS, T_USE, CT::T_USE_TRAIT])) {
return false;
}
}


if ($this->tokens[$prevIndex]->equals('[') && $this->tokens[$nextIndex]->equals(']')) {
$checkToken = $this->tokens[$this->tokens->getNextMeaningfulToken($nextIndex)];

if ($checkToken->equals('"') || $checkToken->isGivenKind([T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES, T_ENCAPSED_AND_WHITESPACE, T_VARIABLE])) {
return false;
}
}


if (AttributeAnalyzer::isAttribute($this->tokens, $index)) {
return false;
}


if ($this->tokens[$nextIndex]->equals(':')) {
if (null === $this->gotoLabelAnalyzer) {
$this->gotoLabelAnalyzer = new GotoLabelAnalyzer();
}

if ($this->gotoLabelAnalyzer->belongsToGoToLabel($this->tokens, $nextIndex)) {
return false;
}
}



while ($this->tokens[$prevIndex]->isGivenKind([CT::T_NAMESPACE_OPERATOR, T_NS_SEPARATOR, T_STRING, CT::T_TYPE_ALTERNATION])) {
$prevIndex = $this->tokens->getPrevMeaningfulToken($prevIndex);
}

if ($this->tokens[$prevIndex]->equals('(')) {
$prevPrevIndex = $this->tokens->getPrevMeaningfulToken($prevIndex);

if ($this->tokens[$prevPrevIndex]->isGivenKind(T_CATCH)) {
return false;
}
}

return true;
}




public function isUnarySuccessorOperator(int $index): bool
{
static $allowedPrevToken = [
']',
[T_STRING],
[T_VARIABLE],
[CT::T_ARRAY_INDEX_CURLY_BRACE_CLOSE],
[CT::T_DYNAMIC_PROP_BRACE_CLOSE],
[CT::T_DYNAMIC_VAR_BRACE_CLOSE],
];

$tokens = $this->tokens;
$token = $tokens[$index];

if (!$token->isGivenKind([T_INC, T_DEC])) {
return false;
}

$prevToken = $tokens[$tokens->getPrevMeaningfulToken($index)];

return $prevToken->equalsAny($allowedPrevToken);
}




public function isUnaryPredecessorOperator(int $index): bool
{
static $potentialSuccessorOperator = [T_INC, T_DEC];

static $potentialBinaryOperator = ['+', '-', '&', [CT::T_RETURN_REF]];

static $otherOperators;

if (null === $otherOperators) {
$otherOperators = ['!', '~', '@', [T_ELLIPSIS]];
}

static $disallowedPrevTokens;

if (null === $disallowedPrevTokens) {
$disallowedPrevTokens = [
']',
'}',
')',
'"',
'`',
[CT::T_ARRAY_SQUARE_BRACE_CLOSE],
[CT::T_ARRAY_INDEX_CURLY_BRACE_CLOSE],
[CT::T_DYNAMIC_PROP_BRACE_CLOSE],
[CT::T_DYNAMIC_VAR_BRACE_CLOSE],
[T_CLASS_C],
[T_CONSTANT_ENCAPSED_STRING],
[T_DEC],
[T_DIR],
[T_DNUMBER],
[T_FILE],
[T_FUNC_C],
[T_INC],
[T_LINE],
[T_LNUMBER],
[T_METHOD_C],
[T_NS_C],
[T_STRING],
[T_TRAIT_C],
[T_VARIABLE],
];
}

$tokens = $this->tokens;
$token = $tokens[$index];

if ($token->isGivenKind($potentialSuccessorOperator)) {
return !$this->isUnarySuccessorOperator($index);
}

if ($token->equalsAny($otherOperators)) {
return true;
}

if (!$token->equalsAny($potentialBinaryOperator)) {
return false;
}

$prevToken = $tokens[$tokens->getPrevMeaningfulToken($index)];

if (!$prevToken->equalsAny($disallowedPrevTokens)) {
return true;
}

if (!$token->equals('&') || !$prevToken->isGivenKind(T_STRING)) {
return false;
}

static $searchTokens = [
';',
'{',
'}',
[T_DOUBLE_ARROW],
[T_FN],
[T_FUNCTION],
[T_OPEN_TAG],
[T_OPEN_TAG_WITH_ECHO],
];
$prevToken = $tokens[$tokens->getPrevTokenOfKind($index, $searchTokens)];

return $prevToken->isGivenKind([T_FN, T_FUNCTION]);
}




public function isBinaryOperator(int $index): bool
{
static $nonArrayOperators = [
'=' => true,
'*' => true,
'/' => true,
'%' => true,
'<' => true,
'>' => true,
'|' => true,
'^' => true,
'.' => true,
];

static $potentialUnaryNonArrayOperators = [
'+' => true,
'-' => true,
'&' => true,
];

static $arrayOperators;

if (null === $arrayOperators) {
$arrayOperators = [
T_AND_EQUAL => true, 
T_BOOLEAN_AND => true, 
T_BOOLEAN_OR => true, 
T_CONCAT_EQUAL => true, 
T_DIV_EQUAL => true, 
T_DOUBLE_ARROW => true, 
T_IS_EQUAL => true, 
T_IS_GREATER_OR_EQUAL => true, 
T_IS_IDENTICAL => true, 
T_IS_NOT_EQUAL => true, 
T_IS_NOT_IDENTICAL => true, 
T_IS_SMALLER_OR_EQUAL => true, 
T_LOGICAL_AND => true, 
T_LOGICAL_OR => true, 
T_LOGICAL_XOR => true, 
T_MINUS_EQUAL => true, 
T_MOD_EQUAL => true, 
T_MUL_EQUAL => true, 
T_OR_EQUAL => true, 
T_PLUS_EQUAL => true, 
T_POW => true, 
T_POW_EQUAL => true, 
T_SL => true, 
T_SL_EQUAL => true, 
T_SR => true, 
T_SR_EQUAL => true, 
T_XOR_EQUAL => true, 
T_SPACESHIP => true, 
T_COALESCE => true, 
T_COALESCE_EQUAL => true, 
];
}

$tokens = $this->tokens;
$token = $tokens[$index];

if ($token->isGivenKind([T_INLINE_HTML, T_ENCAPSED_AND_WHITESPACE, CT::T_TYPE_INTERSECTION])) {
return false;
}

if (isset($potentialUnaryNonArrayOperators[$token->getContent()])) {
return !$this->isUnaryPredecessorOperator($index);
}

if ($token->isArray()) {
return isset($arrayOperators[$token->getId()]);
}

if (isset($nonArrayOperators[$token->getContent()])) {
return true;
}

return false;
}





public function isWhilePartOfDoWhile(int $index): bool
{
$tokens = $this->tokens;
$token = $tokens[$index];

if (!$token->isGivenKind(T_WHILE)) {
throw new \LogicException(\sprintf('No T_WHILE at given index %d, got "%s".', $index, $token->getName()));
}

$endIndex = $tokens->getPrevMeaningfulToken($index);
if (!$tokens[$endIndex]->equals('}')) {
return false;
}

$startIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_CURLY_BRACE, $endIndex);
$beforeStartIndex = $tokens->getPrevMeaningfulToken($startIndex);

return $tokens[$beforeStartIndex]->isGivenKind(T_DO);
}




public function isEnumCase(int $caseIndex): bool
{
$tokens = $this->tokens;
$token = $tokens[$caseIndex];

if (!$token->isGivenKind(T_CASE)) {
throw new \LogicException(\sprintf(
'No T_CASE given at index %d, got %s instead.',
$caseIndex,
$token->getName() ?? $token->getContent()
));
}

if (!\defined('T_ENUM') || !$tokens->isTokenKindFound(T_ENUM)) {
return false;
}

$prevIndex = $tokens->getPrevTokenOfKind($caseIndex, [[T_ENUM], [T_SWITCH]]);

return null !== $prevIndex && $tokens[$prevIndex]->isGivenKind(T_ENUM);
}

public function isSuperGlobal(int $index): bool
{
static $superNames = [
'$_COOKIE' => true,
'$_ENV' => true,
'$_FILES' => true,
'$_GET' => true,
'$_POST' => true,
'$_REQUEST' => true,
'$_SERVER' => true,
'$_SESSION' => true,
'$GLOBALS' => true,
];

$token = $this->tokens[$index];

if (!$token->isGivenKind(T_VARIABLE)) {
return false;
}

return isset($superNames[strtoupper($token->getContent())]);
}











private function findClassyElements(int $classIndex, int $index): array
{
$elements = [];
$curlyBracesLevel = 0;
$bracesLevel = 0;
++$index; 

for ($count = \count($this->tokens); $index < $count; ++$index) {
$token = $this->tokens[$index];

if ($token->isGivenKind(T_ENCAPSED_AND_WHITESPACE)) {
continue;
}

if ($token->isGivenKind(T_CLASS)) { 




$nestedClassIndex = $index;
$index = $this->tokens->getNextMeaningfulToken($index);

if ($this->tokens[$index]->equals('(')) {
++$index; 

for ($nestedBracesLevel = 1; $index < $count; ++$index) {
$token = $this->tokens[$index];

if ($token->equals('(')) {
++$nestedBracesLevel;

continue;
}

if ($token->equals(')')) {
--$nestedBracesLevel;

if (0 === $nestedBracesLevel) {
[$index, $newElements] = $this->findClassyElements($nestedClassIndex, $index);
$elements += $newElements;

break;
}

continue;
}

if ($token->isGivenKind(T_CLASS)) { 
[$index, $newElements] = $this->findClassyElements($index, $index);
$elements += $newElements;
}
}
} else {
[$index, $newElements] = $this->findClassyElements($nestedClassIndex, $nestedClassIndex);
$elements += $newElements;
}

continue;
}

if ($token->equals('(')) {
++$bracesLevel;

continue;
}

if ($token->equals(')')) {
--$bracesLevel;

continue;
}

if ($token->equals('{')) {
++$curlyBracesLevel;

continue;
}

if ($token->equals('}')) {
--$curlyBracesLevel;

if (0 === $curlyBracesLevel) {
break;
}

continue;
}

if (1 !== $curlyBracesLevel || !$token->isArray()) {
continue;
}

if (0 === $bracesLevel && $token->isGivenKind(T_VARIABLE)) {
$elements[$index] = [
'classIndex' => $classIndex,
'token' => $token,
'type' => 'property',
];

continue;
}

if ($token->isGivenKind(T_FUNCTION)) {
$elements[$index] = [
'classIndex' => $classIndex,
'token' => $token,
'type' => 'method',
];
} elseif ($token->isGivenKind(T_CONST)) {
$elements[$index] = [
'classIndex' => $classIndex,
'token' => $token,
'type' => 'const',
];
} elseif ($token->isGivenKind(CT::T_USE_TRAIT)) {
$elements[$index] = [
'classIndex' => $classIndex,
'token' => $token,
'type' => 'trait_import',
];
} elseif ($token->isGivenKind(T_CASE)) {
$elements[$index] = [
'classIndex' => $classIndex,
'token' => $token,
'type' => 'case',
];
}
}

return [$index, $elements];
}
}
