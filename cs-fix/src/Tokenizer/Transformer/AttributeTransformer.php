<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;






final class AttributeTransformer extends AbstractTransformer
{
public function getPriority(): int
{

return 200;
}

public function getRequiredPhpVersionId(): int
{
return 8_00_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if (!$tokens[$index]->isGivenKind(T_ATTRIBUTE)) {
return;
}

$level = 1;

do {
++$index;

if ($tokens[$index]->equals('[')) {
++$level;
} elseif ($tokens[$index]->equals(']')) {
--$level;
}
} while (0 < $level);

$tokens[$index] = new Token([CT::T_ATTRIBUTE_CLOSE, ']']);
}

public function getCustomTokens(): array
{
return [
CT::T_ATTRIBUTE_CLOSE,
];
}
}
