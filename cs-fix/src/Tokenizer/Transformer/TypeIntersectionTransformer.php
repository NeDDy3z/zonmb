<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTypeTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;







final class TypeIntersectionTransformer extends AbstractTypeTransformer
{
public function getPriority(): int
{

return -15;
}

public function getRequiredPhpVersionId(): int
{
return 8_01_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
$this->doProcess($tokens, $index, [T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG, '&']);
}

public function getCustomTokens(): array
{
return [CT::T_TYPE_INTERSECTION];
}

protected function replaceToken(Tokens $tokens, int $index): void
{
$tokens[$index] = new Token([CT::T_TYPE_INTERSECTION, '&']);
}
}
