<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\Analyzer\Analysis\ArgumentAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\TypeAnalysis;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class FunctionsAnalyzer
{



private array $functionsAnalysis = ['tokens' => '', 'imports' => [], 'declarations' => []];




public function isGlobalFunctionCall(Tokens $tokens, int $index): bool
{
if (!$tokens[$index]->isGivenKind(T_STRING)) {
return false;
}

$nextIndex = $tokens->getNextMeaningfulToken($index);

if (!$tokens[$nextIndex]->equals('(')) {
return false;
}

$previousIsNamespaceSeparator = false;
$prevIndex = $tokens->getPrevMeaningfulToken($index);

if ($tokens[$prevIndex]->isGivenKind(T_NS_SEPARATOR)) {
$previousIsNamespaceSeparator = true;
$prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
}

$possibleKind = [
T_DOUBLE_COLON, T_FUNCTION, CT::T_NAMESPACE_OPERATOR, T_NEW, CT::T_RETURN_REF, T_STRING,
...Token::getObjectOperatorKinds(),
];


if (\defined('T_ATTRIBUTE')) {
$possibleKind[] = T_ATTRIBUTE;
}

if ($tokens[$prevIndex]->isGivenKind($possibleKind)) {
return false;
}

if ($tokens[$tokens->getNextMeaningfulToken($nextIndex)]->isGivenKind(CT::T_FIRST_CLASS_CALLABLE)) {
return false;
}

if ($previousIsNamespaceSeparator) {
return true;
}

if ($tokens->isChanged() || $tokens->getCodeHash() !== $this->functionsAnalysis['tokens']) {
$this->buildFunctionsAnalysis($tokens);
}


$scopeStartIndex = 0;
$scopeEndIndex = \count($tokens) - 1;
$inGlobalNamespace = false;

foreach ($tokens->getNamespaceDeclarations() as $declaration) {
$scopeStartIndex = $declaration->getScopeStartIndex();
$scopeEndIndex = $declaration->getScopeEndIndex();

if ($index >= $scopeStartIndex && $index <= $scopeEndIndex) {
$inGlobalNamespace = $declaration->isGlobalNamespace();

break;
}
}

$call = strtolower($tokens[$index]->getContent());





if (!$inGlobalNamespace) {

foreach ($this->functionsAnalysis['declarations'] as $functionNameIndex) {
if ($functionNameIndex < $scopeStartIndex || $functionNameIndex > $scopeEndIndex) {
continue;
}

if (strtolower($tokens[$functionNameIndex]->getContent()) === $call) {
return false;
}
}
}


foreach ($this->functionsAnalysis['imports'] as $functionUse) {
if ($functionUse->getStartIndex() < $scopeStartIndex || $functionUse->getEndIndex() > $scopeEndIndex) {
continue;
}

if ($call !== strtolower($functionUse->getShortName())) {
continue;
}


return $functionUse->getShortName() === ltrim($functionUse->getFullName(), '\\');
}

if (AttributeAnalyzer::isAttribute($tokens, $index)) {
return false;
}

return true;
}




public function getFunctionArguments(Tokens $tokens, int $functionIndex): array
{
$argumentsStart = $tokens->getNextTokenOfKind($functionIndex, ['(']);
$argumentsEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $argumentsStart);
$argumentAnalyzer = new ArgumentsAnalyzer();
$arguments = [];

foreach ($argumentAnalyzer->getArguments($tokens, $argumentsStart, $argumentsEnd) as $start => $end) {
$argumentInfo = $argumentAnalyzer->getArgumentInfo($tokens, $start, $end);
$arguments[$argumentInfo->getName()] = $argumentInfo;
}

return $arguments;
}

public function getFunctionReturnType(Tokens $tokens, int $methodIndex): ?TypeAnalysis
{
$argumentsStart = $tokens->getNextTokenOfKind($methodIndex, ['(']);
$argumentsEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $argumentsStart);
$typeColonIndex = $tokens->getNextMeaningfulToken($argumentsEnd);

if (!$tokens[$typeColonIndex]->isGivenKind(CT::T_TYPE_COLON)) {
return null;
}

$type = '';
$typeStartIndex = $tokens->getNextMeaningfulToken($typeColonIndex);
$typeEndIndex = $typeStartIndex;
$functionBodyStart = $tokens->getNextTokenOfKind($typeColonIndex, ['{', ';', [T_DOUBLE_ARROW]]);

for ($i = $typeStartIndex; $i < $functionBodyStart; ++$i) {
if ($tokens[$i]->isWhitespace() || $tokens[$i]->isComment()) {
continue;
}

$type .= $tokens[$i]->getContent();
$typeEndIndex = $i;
}

return new TypeAnalysis($type, $typeStartIndex, $typeEndIndex);
}

public function isTheSameClassCall(Tokens $tokens, int $index): bool
{
if (!$tokens->offsetExists($index)) {
throw new \InvalidArgumentException(\sprintf('Token index %d does not exist.', $index));
}

$operatorIndex = $tokens->getPrevMeaningfulToken($index);

if (null === $operatorIndex) {
return false;
}

if (!$tokens[$operatorIndex]->isObjectOperator() && !$tokens[$operatorIndex]->isGivenKind(T_DOUBLE_COLON)) {
return false;
}

$referenceIndex = $tokens->getPrevMeaningfulToken($operatorIndex);

if (null === $referenceIndex) {
return false;
}

if (!$tokens[$referenceIndex]->equalsAny([[T_VARIABLE, '$this'], [T_STRING, 'self'], [T_STATIC, 'static']], false)) {
return false;
}

return $tokens[$tokens->getNextMeaningfulToken($index)]->equals('(');
}

private function buildFunctionsAnalysis(Tokens $tokens): void
{
$this->functionsAnalysis = [
'tokens' => $tokens->getCodeHash(),
'imports' => [],
'declarations' => [],
];



if ($tokens->isTokenKindFound(T_FUNCTION)) {
$end = \count($tokens);

for ($i = 0; $i < $end; ++$i) {

if ($tokens[$i]->isGivenKind(Token::getClassyTokenKinds())) {
$i = $tokens->getNextTokenOfKind($i, ['(', '{']);

if ($tokens[$i]->equals('(')) { 
$i = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $i);
$i = $tokens->getNextTokenOfKind($i, ['{']);
}

$i = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $i);

continue;
}

if (!$tokens[$i]->isGivenKind(T_FUNCTION)) {
continue;
}

$i = $tokens->getNextMeaningfulToken($i);

if ($tokens[$i]->isGivenKind(CT::T_RETURN_REF)) {
$i = $tokens->getNextMeaningfulToken($i);
}

if (!$tokens[$i]->isGivenKind(T_STRING)) {
continue;
}

$this->functionsAnalysis['declarations'][] = $i;
}
}



$namespaceUsesAnalyzer = new NamespaceUsesAnalyzer();

if ($tokens->isTokenKindFound(CT::T_FUNCTION_IMPORT)) {
$declarations = $namespaceUsesAnalyzer->getDeclarationsFromTokens($tokens);

foreach ($declarations as $declaration) {
if ($declaration->isFunction()) {
$this->functionsAnalysis['imports'][] = $declaration;
}
}
}
}
}
