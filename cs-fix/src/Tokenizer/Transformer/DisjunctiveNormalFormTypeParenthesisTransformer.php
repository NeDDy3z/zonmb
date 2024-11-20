<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;








final class DisjunctiveNormalFormTypeParenthesisTransformer extends AbstractTransformer
{
public function getPriority(): int
{

return -16;
}

public function getRequiredPhpVersionId(): int
{
return 8_02_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if ($token->equals('(') && $tokens[$tokens->getPrevMeaningfulToken($index)]->isGivenKind(CT::T_TYPE_ALTERNATION)) {
$openIndex = $index;
$closeIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
} elseif ($token->equals(')') && $tokens[$tokens->getNextMeaningfulToken($index)]->isGivenKind(CT::T_TYPE_ALTERNATION)) {
$openIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
$closeIndex = $index;
} else {
return;
}

$tokens[$openIndex] = new Token([CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN, '(']);
$tokens[$closeIndex] = new Token([CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE, ')']);
}

public function getCustomTokens(): array
{
return [
CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN,
CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE,
];
}
}
