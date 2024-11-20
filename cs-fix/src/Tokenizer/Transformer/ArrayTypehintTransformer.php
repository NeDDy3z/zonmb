<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;








final class ArrayTypehintTransformer extends AbstractTransformer
{
public function getRequiredPhpVersionId(): int
{
return 5_00_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if (!$token->isGivenKind(T_ARRAY)) {
return;
}

$nextIndex = $tokens->getNextMeaningfulToken($index);
$nextToken = $tokens[$nextIndex];

if (!$nextToken->equals('(')) {
$tokens[$index] = new Token([CT::T_ARRAY_TYPEHINT, $token->getContent()]);
}
}

public function getCustomTokens(): array
{
return [CT::T_ARRAY_TYPEHINT];
}
}
