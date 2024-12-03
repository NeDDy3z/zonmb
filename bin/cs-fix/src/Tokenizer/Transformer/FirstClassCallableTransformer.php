<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class FirstClassCallableTransformer extends AbstractTransformer
{
public function getRequiredPhpVersionId(): int
{
return 8_01_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if (
$token->isGivenKind(T_ELLIPSIS)
&& $tokens[$tokens->getPrevMeaningfulToken($index)]->equals('(')
&& $tokens[$tokens->getNextMeaningfulToken($index)]->equals(')')
) {
$tokens[$index] = new Token([CT::T_FIRST_CLASS_CALLABLE, '...']);
}
}

public function getCustomTokens(): array
{
return [
CT::T_FIRST_CLASS_CALLABLE,
];
}
}
