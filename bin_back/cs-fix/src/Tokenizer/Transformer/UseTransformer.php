<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;










final class UseTransformer extends AbstractTransformer
{
public function getPriority(): int
{

return -5;
}

public function getRequiredPhpVersionId(): int
{
return 5_03_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if ($token->isGivenKind(T_USE) && $this->isUseForLambda($tokens, $index)) {
$tokens[$index] = new Token([CT::T_USE_LAMBDA, $token->getContent()]);

return;
}




$classTypes = [T_TRAIT];

if (\defined('T_ENUM')) { 
$classTypes[] = T_ENUM;
}

if ($token->isGivenKind(T_CLASS)) {
if ($tokens[$tokens->getPrevMeaningfulToken($index)]->isGivenKind(T_DOUBLE_COLON)) {
return;
}
} elseif (!$token->isGivenKind($classTypes)) {
return;
}

$index = $tokens->getNextTokenOfKind($index, ['{']);
$innerLimit = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $index);

while ($index < $innerLimit) {
$token = $tokens[++$index];

if (!$token->isGivenKind(T_USE)) {
continue;
}

if ($this->isUseForLambda($tokens, $index)) {
$tokens[$index] = new Token([CT::T_USE_LAMBDA, $token->getContent()]);
} else {
$tokens[$index] = new Token([CT::T_USE_TRAIT, $token->getContent()]);
}
}
}

public function getCustomTokens(): array
{
return [CT::T_USE_TRAIT, CT::T_USE_LAMBDA];
}




private function isUseForLambda(Tokens $tokens, int $index): bool
{
$nextToken = $tokens[$tokens->getNextMeaningfulToken($index)];


return $nextToken->equals('(');
}
}
