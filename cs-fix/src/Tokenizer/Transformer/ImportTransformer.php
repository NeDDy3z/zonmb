<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;












final class ImportTransformer extends AbstractTransformer
{
public function getPriority(): int
{

return -1;
}

public function getRequiredPhpVersionId(): int
{
return 5_06_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if (!$token->isGivenKind([T_CONST, T_FUNCTION])) {
return;
}

$prevToken = $tokens[$tokens->getPrevMeaningfulToken($index)];

if (!$prevToken->isGivenKind(T_USE)) {
$nextToken = $tokens[$tokens->getNextTokenOfKind($index, ['=', '(', [CT::T_RETURN_REF], [CT::T_GROUP_IMPORT_BRACE_CLOSE]])];

if (!$nextToken->isGivenKind(CT::T_GROUP_IMPORT_BRACE_CLOSE)) {
return;
}
}

$tokens[$index] = new Token([
$token->isGivenKind(T_FUNCTION) ? CT::T_FUNCTION_IMPORT : CT::T_CONST_IMPORT,
$token->getContent(),
]);
}

public function getCustomTokens(): array
{
return [CT::T_CONST_IMPORT, CT::T_FUNCTION_IMPORT];
}
}
