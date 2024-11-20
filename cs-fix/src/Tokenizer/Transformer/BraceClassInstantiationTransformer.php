<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;









final class BraceClassInstantiationTransformer extends AbstractTransformer
{
public function getPriority(): int
{

return -2;
}

public function getRequiredPhpVersionId(): int
{
return 5_00_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if (!$tokens[$index]->equals('(') || !$tokens[$tokens->getNextMeaningfulToken($index)]->isGivenKind(T_NEW)) {
return;
}

if ($tokens[$tokens->getPrevMeaningfulToken($index)]->equalsAny([
')',
']',
[CT::T_ARRAY_INDEX_CURLY_BRACE_CLOSE],
[CT::T_ARRAY_SQUARE_BRACE_CLOSE],
[CT::T_BRACE_CLASS_INSTANTIATION_CLOSE],
[T_ARRAY],
[T_CLASS],
[T_ELSEIF],
[T_FOR],
[T_FOREACH],
[T_IF],
[T_STATIC],
[T_STRING],
[T_SWITCH],
[T_VARIABLE],
[T_WHILE],
])) {
return;
}

$closeIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);

$tokens[$index] = new Token([CT::T_BRACE_CLASS_INSTANTIATION_OPEN, '(']);
$tokens[$closeIndex] = new Token([CT::T_BRACE_CLASS_INSTANTIATION_CLOSE, ')']);
}

public function getCustomTokens(): array
{
return [CT::T_BRACE_CLASS_INSTANTIATION_OPEN, CT::T_BRACE_CLASS_INSTANTIATION_CLOSE];
}
}
