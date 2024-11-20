<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;








final class ClassConstantTransformer extends AbstractTransformer
{
public function getRequiredPhpVersionId(): int
{
return 5_05_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if (!$token->equalsAny([
[T_CLASS, 'class'],
[T_STRING, 'class'],
], false)) {
return;
}

$prevIndex = $tokens->getPrevMeaningfulToken($index);
$prevToken = $tokens[$prevIndex];

if ($prevToken->isGivenKind(T_DOUBLE_COLON)) {
$tokens[$index] = new Token([CT::T_CLASS_CONSTANT, $token->getContent()]);
}
}

public function getCustomTokens(): array
{
return [CT::T_CLASS_CONSTANT];
}
}
