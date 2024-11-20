<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\Tokens;




final class WhitespacesAnalyzer
{
public static function detectIndent(Tokens $tokens, int $index): string
{
while (true) {
$whitespaceIndex = $tokens->getPrevTokenOfKind($index, [[T_WHITESPACE]]);

if (null === $whitespaceIndex) {
return '';
}

$whitespaceToken = $tokens[$whitespaceIndex];

if (str_contains($whitespaceToken->getContent(), "\n")) {
break;
}

$prevToken = $tokens[$whitespaceIndex - 1];

if ($prevToken->isGivenKind([T_OPEN_TAG, T_COMMENT]) && "\n" === substr($prevToken->getContent(), -1)) {
break;
}

$index = $whitespaceIndex;
}

$explodedContent = explode("\n", $whitespaceToken->getContent());

return end($explodedContent);
}
}
