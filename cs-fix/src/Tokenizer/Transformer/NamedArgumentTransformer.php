<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;






final class NamedArgumentTransformer extends AbstractTransformer
{
public function getPriority(): int
{

return -15;
}

public function getRequiredPhpVersionId(): int
{
return 8_00_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if (!$tokens[$index]->equals(':')) {
return;
}

$stringIndex = $tokens->getPrevMeaningfulToken($index);

if (!$tokens[$stringIndex]->isGivenKind(T_STRING)) {
return;
}

$preStringIndex = $tokens->getPrevMeaningfulToken($stringIndex);





if (!$tokens[$preStringIndex]->equalsAny([',', '('])) {
return;
}

$tokens[$stringIndex] = new Token([CT::T_NAMED_ARGUMENT_NAME, $tokens[$stringIndex]->getContent()]);
$tokens[$index] = new Token([CT::T_NAMED_ARGUMENT_COLON, ':']);
}

public function getCustomTokens(): array
{
return [
CT::T_NAMED_ARGUMENT_COLON,
CT::T_NAMED_ARGUMENT_NAME,
];
}
}
