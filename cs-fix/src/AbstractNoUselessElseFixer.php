<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\Tokenizer\Tokens;

abstract class AbstractNoUselessElseFixer extends AbstractFixer
{
public function getPriority(): int
{

return 39;
}

protected function isSuperfluousElse(Tokens $tokens, int $index): bool
{
$previousBlockStart = $index;

do {


[$previousBlockStart, $previousBlockEnd] = $this->getPreviousBlock($tokens, $previousBlockStart);


$previous = $previousBlockEnd;
if ($tokens[$previous]->equals('}')) {
$previous = $tokens->getPrevMeaningfulToken($previous);
}

if (
!$tokens[$previous]->equals(';') 
|| $tokens[$tokens->getPrevMeaningfulToken($previous)]->equals('{') 
) {
return false;
}

$candidateIndex = $tokens->getPrevTokenOfKind(
$previous,
[
';',
[T_BREAK],
[T_CLOSE_TAG],
[T_CONTINUE],
[T_EXIT],
[T_GOTO],
[T_IF],
[T_RETURN],
[T_THROW],
]
);

if (null === $candidateIndex || $tokens[$candidateIndex]->equalsAny([';', [T_CLOSE_TAG], [T_IF]])) {
return false;
}

if ($tokens[$candidateIndex]->isGivenKind(T_THROW)) {
$previousIndex = $tokens->getPrevMeaningfulToken($candidateIndex);

if (!$tokens[$previousIndex]->equalsAny([';', '{'])) {
return false;
}
}

if ($this->isInConditional($tokens, $candidateIndex, $previousBlockStart)
|| $this->isInConditionWithoutBraces($tokens, $candidateIndex, $previousBlockStart)
) {
return false;
}


} while (!$tokens[$previousBlockStart]->isGivenKind(T_IF));

return true;
}











private function getPreviousBlock(Tokens $tokens, int $index): array
{
$close = $previous = $tokens->getPrevMeaningfulToken($index);

if ($tokens[$close]->equals('}')) {
$previous = $tokens->findBlockStart(Tokens::BLOCK_TYPE_CURLY_BRACE, $close);
}

$open = $tokens->getPrevTokenOfKind($previous, [[T_IF], [T_ELSE], [T_ELSEIF]]);
if ($tokens[$open]->isGivenKind(T_IF)) {
$elseCandidate = $tokens->getPrevMeaningfulToken($open);
if ($tokens[$elseCandidate]->isGivenKind(T_ELSE)) {
$open = $elseCandidate;
}
}

return [$open, $close];
}





private function isInConditional(Tokens $tokens, int $index, int $lowerLimitIndex): bool
{
$candidateIndex = $tokens->getPrevTokenOfKind($index, [')', ';', ':']);
if ($tokens[$candidateIndex]->equals(':')) {
return true;
}

if (!$tokens[$candidateIndex]->equals(')')) {
return false; 
}




$open = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $candidateIndex);

return $tokens->getPrevMeaningfulToken($open) > $lowerLimitIndex;
}










private function isInConditionWithoutBraces(Tokens $tokens, int $index, int $lowerLimitIndex): bool
{
do {
if ($tokens[$index]->isComment() || $tokens[$index]->isWhitespace()) {
$index = $tokens->getPrevMeaningfulToken($index);
}

$token = $tokens[$index];
if ($token->isGivenKind([T_IF, T_ELSEIF, T_ELSE])) {
return true;
}

if ($token->equals(';')) {
return false;
}

if ($token->equals('{')) {
$index = $tokens->getPrevMeaningfulToken($index);



if ($tokens[$index]->isGivenKind(T_DO)) {
--$index;

continue;
}

if (!$tokens[$index]->equals(')')) {
return false; 
}

$index = $tokens->findBlockStart(
Tokens::BLOCK_TYPE_PARENTHESIS_BRACE,
$index
);

$index = $tokens->getPrevMeaningfulToken($index);
if ($tokens[$index]->isGivenKind([T_IF, T_ELSEIF])) {
return false;
}
} elseif ($token->equals(')')) {
$type = Tokens::detectBlockType($token);
$index = $tokens->findBlockStart(
$type['type'],
$index
);

$index = $tokens->getPrevMeaningfulToken($index);
} else {
--$index;
}
} while ($index > $lowerLimitIndex);

return false;
}
}
