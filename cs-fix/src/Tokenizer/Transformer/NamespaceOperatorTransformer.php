<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;








final class NamespaceOperatorTransformer extends AbstractTransformer
{
public function getRequiredPhpVersionId(): int
{
return 5_03_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if (!$token->isGivenKind(T_NAMESPACE)) {
return;
}

$nextIndex = $tokens->getNextMeaningfulToken($index);

if ($tokens[$nextIndex]->isGivenKind(T_NS_SEPARATOR)) {
$tokens[$index] = new Token([CT::T_NAMESPACE_OPERATOR, $token->getContent()]);
}
}

public function getCustomTokens(): array
{
return [CT::T_NAMESPACE_OPERATOR];
}
}
