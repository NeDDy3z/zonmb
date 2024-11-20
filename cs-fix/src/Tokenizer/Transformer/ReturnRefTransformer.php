<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;








final class ReturnRefTransformer extends AbstractTransformer
{
public function getRequiredPhpVersionId(): int
{
return 5_00_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if ($token->equals('&') && $tokens[$tokens->getPrevMeaningfulToken($index)]->isGivenKind([T_FUNCTION, T_FN])) {
$tokens[$index] = new Token([CT::T_RETURN_REF, '&']);
}
}

public function getCustomTokens(): array
{
return [CT::T_RETURN_REF];
}
}
