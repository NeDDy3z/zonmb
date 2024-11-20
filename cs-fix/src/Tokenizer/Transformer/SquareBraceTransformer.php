<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;












final class SquareBraceTransformer extends AbstractTransformer
{
public function getPriority(): int
{

return -1;
}

public function getRequiredPhpVersionId(): int
{



return 5_00_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if ($this->isArrayDestructing($tokens, $index)) {
$this->transformIntoDestructuringSquareBrace($tokens, $index);

return;
}

if ($this->isShortArray($tokens, $index)) {
$this->transformIntoArraySquareBrace($tokens, $index);
}
}

public function getCustomTokens(): array
{
return [
CT::T_ARRAY_SQUARE_BRACE_OPEN,
CT::T_ARRAY_SQUARE_BRACE_CLOSE,
CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN,
CT::T_DESTRUCTURING_SQUARE_BRACE_CLOSE,
];
}

private function transformIntoArraySquareBrace(Tokens $tokens, int $index): void
{
$endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_INDEX_SQUARE_BRACE, $index);

$tokens[$index] = new Token([CT::T_ARRAY_SQUARE_BRACE_OPEN, '[']);
$tokens[$endIndex] = new Token([CT::T_ARRAY_SQUARE_BRACE_CLOSE, ']']);
}

private function transformIntoDestructuringSquareBrace(Tokens $tokens, int $index): void
{
$endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_INDEX_SQUARE_BRACE, $index);

$tokens[$index] = new Token([CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN, '[']);
$tokens[$endIndex] = new Token([CT::T_DESTRUCTURING_SQUARE_BRACE_CLOSE, ']']);

$previousMeaningfulIndex = $index;
$index = $tokens->getNextMeaningfulToken($index);

while ($index < $endIndex) {
if ($tokens[$index]->equals('[') && $tokens[$previousMeaningfulIndex]->equalsAny([[CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN], ','])) {
$tokens[$tokens->findBlockEnd(Tokens::BLOCK_TYPE_INDEX_SQUARE_BRACE, $index)] = new Token([CT::T_DESTRUCTURING_SQUARE_BRACE_CLOSE, ']']);
$tokens[$index] = new Token([CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN, '[']);
}

$previousMeaningfulIndex = $index;
$index = $tokens->getNextMeaningfulToken($index);
}
}




private function isShortArray(Tokens $tokens, int $index): bool
{
if (!$tokens[$index]->equals('[')) {
return false;
}

static $disallowedPrevTokens = [
')',
']',
'}',
'"',
[T_CONSTANT_ENCAPSED_STRING],
[T_STRING],
[T_STRING_VARNAME],
[T_VARIABLE],
[CT::T_ARRAY_SQUARE_BRACE_CLOSE],
[CT::T_DYNAMIC_PROP_BRACE_CLOSE],
[CT::T_DYNAMIC_VAR_BRACE_CLOSE],
[CT::T_ARRAY_INDEX_CURLY_BRACE_CLOSE],
];

$prevToken = $tokens[$tokens->getPrevMeaningfulToken($index)];
if ($prevToken->equalsAny($disallowedPrevTokens)) {
return false;
}

$nextToken = $tokens[$tokens->getNextMeaningfulToken($index)];
if ($nextToken->equals(']')) {
return true;
}

return !$this->isArrayDestructing($tokens, $index);
}

private function isArrayDestructing(Tokens $tokens, int $index): bool
{
if (!$tokens[$index]->equals('[')) {
return false;
}

static $disallowedPrevTokens = [
')',
']',
'"',
[T_CONSTANT_ENCAPSED_STRING],
[T_STRING],
[T_STRING_VARNAME],
[T_VARIABLE],
[CT::T_ARRAY_SQUARE_BRACE_CLOSE],
[CT::T_DYNAMIC_PROP_BRACE_CLOSE],
[CT::T_DYNAMIC_VAR_BRACE_CLOSE],
[CT::T_ARRAY_INDEX_CURLY_BRACE_CLOSE],
];

$prevIndex = $tokens->getPrevMeaningfulToken($index);
$prevToken = $tokens[$prevIndex];
if ($prevToken->equalsAny($disallowedPrevTokens)) {
return false;
}

if ($prevToken->isGivenKind(T_AS)) {
return true;
}

if ($prevToken->isGivenKind(T_DOUBLE_ARROW)) {
$variableIndex = $tokens->getPrevMeaningfulToken($prevIndex);
if (!$tokens[$variableIndex]->isGivenKind(T_VARIABLE)) {
return false;
}

$prevVariableIndex = $tokens->getPrevMeaningfulToken($variableIndex);
if ($tokens[$prevVariableIndex]->isGivenKind(T_AS)) {
return true;
}
}

$type = Tokens::detectBlockType($tokens[$index]);
$end = $tokens->findBlockEnd($type['type'], $index);

$nextToken = $tokens[$tokens->getNextMeaningfulToken($end)];

return $nextToken->equals('=');
}
}
