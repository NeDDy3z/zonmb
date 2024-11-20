<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\Annotation;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;




final class VoidReturnFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Add `void` return type to functions with missing or empty return statements, but priority is given to `@return` annotations. Requires PHP >= 7.1.',
[
new CodeSample(
"<?php\nfunction foo(\$a) {};\n"
),
],
null,
'Modifies the signature of functions.'
);
}







public function getPriority(): int
{
return 5;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_FUNCTION);
}

public function isRisky(): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{

static $excludedFunctions = [
[T_STRING, '__clone'],
[T_STRING, '__construct'],
[T_STRING, '__debugInfo'],
[T_STRING, '__destruct'],
[T_STRING, '__isset'],
[T_STRING, '__serialize'],
[T_STRING, '__set_state'],
[T_STRING, '__sleep'],
[T_STRING, '__toString'],
];

for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
continue;
}

$functionName = $tokens->getNextMeaningfulToken($index);
if ($tokens[$functionName]->equalsAny($excludedFunctions, false)) {
continue;
}

$startIndex = $tokens->getNextTokenOfKind($index, ['{', ';']);

if ($this->hasReturnTypeHint($tokens, $startIndex)) {
continue;
}

if ($tokens[$startIndex]->equals(';')) {

if ($this->hasVoidReturnAnnotation($tokens, $index)) {
$this->fixFunctionDefinition($tokens, $startIndex);
}

continue;
}

if ($this->hasReturnAnnotation($tokens, $index)) {
continue;
}

$endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $startIndex);

if ($this->hasVoidReturn($tokens, $startIndex, $endIndex)) {
$this->fixFunctionDefinition($tokens, $startIndex);
}
}
}






private function hasReturnAnnotation(Tokens $tokens, int $index): bool
{
foreach ($this->findReturnAnnotations($tokens, $index) as $return) {
if (['void'] !== $return->getTypes()) {
return true;
}
}

return false;
}






private function hasVoidReturnAnnotation(Tokens $tokens, int $index): bool
{
foreach ($this->findReturnAnnotations($tokens, $index) as $return) {
if (['void'] === $return->getTypes()) {
return true;
}
}

return false;
}






private function hasReturnTypeHint(Tokens $tokens, int $index): bool
{
$endFuncIndex = $tokens->getPrevTokenOfKind($index, [')']);
$nextIndex = $tokens->getNextMeaningfulToken($endFuncIndex);

return $tokens[$nextIndex]->isGivenKind(CT::T_TYPE_COLON);
}







private function hasVoidReturn(Tokens $tokens, int $startIndex, int $endIndex): bool
{
$tokensAnalyzer = new TokensAnalyzer($tokens);

for ($i = $startIndex; $i < $endIndex; ++$i) {
if (

($tokens[$i]->isGivenKind(T_CLASS) && $tokensAnalyzer->isAnonymousClass($i))

|| ($tokens[$i]->isGivenKind(T_FUNCTION) && $tokensAnalyzer->isLambda($i))
) {
$i = $tokens->getNextTokenOfKind($i, ['{']);
$i = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $i);

continue;
}

if ($tokens[$i]->isGivenKind([T_YIELD, T_YIELD_FROM])) {
return false; 
}

if (!$tokens[$i]->isGivenKind(T_RETURN)) {
continue;
}

$i = $tokens->getNextMeaningfulToken($i);
if (!$tokens[$i]->equals(';')) {
return false;
}
}

return true;
}




private function fixFunctionDefinition(Tokens $tokens, int $index): void
{
$endFuncIndex = $tokens->getPrevTokenOfKind($index, [')']);
$tokens->insertAt($endFuncIndex + 1, [
new Token([CT::T_TYPE_COLON, ':']),
new Token([T_WHITESPACE, ' ']),
new Token([T_STRING, 'void']),
]);
}








private function findReturnAnnotations(Tokens $tokens, int $index): array
{
$previousTokens = [
T_ABSTRACT,
T_FINAL,
T_PRIVATE,
T_PROTECTED,
T_PUBLIC,
T_STATIC,
];

if (\defined('T_ATTRIBUTE')) { 
$previousTokens[] = T_ATTRIBUTE;
}

do {
$index = $tokens->getPrevNonWhitespace($index);

if ($tokens[$index]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
$index = $tokens->getPrevTokenOfKind($index, [[T_ATTRIBUTE]]);
}
} while ($tokens[$index]->isGivenKind($previousTokens));

if (!$tokens[$index]->isGivenKind(T_DOC_COMMENT)) {
return [];
}

$doc = new DocBlock($tokens[$index]->getContent());

return $doc->getAnnotationsOfType('return');
}
}
