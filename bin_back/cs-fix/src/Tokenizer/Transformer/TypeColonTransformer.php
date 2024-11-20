<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;








final class TypeColonTransformer extends AbstractTransformer
{
public function getPriority(): int
{


return -10;
}

public function getRequiredPhpVersionId(): int
{
return 7_00_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if (!$token->equals(':')) {
return;
}

$endIndex = $tokens->getPrevMeaningfulToken($index);

if (
\defined('T_ENUM') 
&& $tokens[$tokens->getPrevMeaningfulToken($endIndex)]->isGivenKind(T_ENUM)
) {
$tokens[$index] = new Token([CT::T_TYPE_COLON, ':']);

return;
}

if (!$tokens[$endIndex]->equals(')')) {
return;
}

$startIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $endIndex);
$prevIndex = $tokens->getPrevMeaningfulToken($startIndex);
$prevToken = $tokens[$prevIndex];


if ($prevToken->isGivenKind(T_STRING)) {
$prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
$prevToken = $tokens[$prevIndex];
}

if ($prevToken->isGivenKind([T_FUNCTION, CT::T_RETURN_REF, CT::T_USE_LAMBDA, T_FN])) {
$tokens[$index] = new Token([CT::T_TYPE_COLON, ':']);
}
}

public function getCustomTokens(): array
{
return [CT::T_TYPE_COLON];
}
}
