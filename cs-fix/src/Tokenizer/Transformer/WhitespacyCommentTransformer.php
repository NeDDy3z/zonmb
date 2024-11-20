<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;








final class WhitespacyCommentTransformer extends AbstractTransformer
{
public function getRequiredPhpVersionId(): int
{
return 5_00_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if (!$token->isComment()) {
return;
}

$content = $token->getContent();
$trimmedContent = rtrim($content);


if ($content === $trimmedContent) {
return;
}

$whitespaces = substr($content, \strlen($trimmedContent));

$tokens[$index] = new Token([$token->getId(), $trimmedContent]);

if (isset($tokens[$index + 1]) && $tokens[$index + 1]->isWhitespace()) {
$tokens[$index + 1] = new Token([T_WHITESPACE, $whitespaces.$tokens[$index + 1]->getContent()]);
} else {
$tokens->insertAt($index + 1, new Token([T_WHITESPACE, $whitespaces]));
}
}

public function getCustomTokens(): array
{
return [];
}
}
