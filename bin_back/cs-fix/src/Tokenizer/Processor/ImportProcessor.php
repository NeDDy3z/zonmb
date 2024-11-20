<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Processor;

use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;




final class ImportProcessor
{
private WhitespacesFixerConfig $whitespacesConfig;

public function __construct(WhitespacesFixerConfig $whitespacesConfig)
{
$this->whitespacesConfig = $whitespacesConfig;
}








public function insertImports(Tokens $tokens, array $imports, int $atIndex): void
{
$lineEnding = $this->whitespacesConfig->getLineEnding();

if (!$tokens[$atIndex]->isWhitespace() || !str_contains($tokens[$atIndex]->getContent(), "\n")) {
$tokens->insertAt($atIndex, new Token([T_WHITESPACE, $lineEnding]));
}

foreach ($imports as $type => $typeImports) {
sort($typeImports);

$items = [];

foreach ($typeImports as $name) {
$items = array_merge($items, [
new Token([T_WHITESPACE, $lineEnding]),
new Token([T_USE, 'use']),
new Token([T_WHITESPACE, ' ']),
]);

if ('const' === $type) {
$items[] = new Token([CT::T_CONST_IMPORT, 'const']);
$items[] = new Token([T_WHITESPACE, ' ']);
} elseif ('function' === $type) {
$items[] = new Token([CT::T_FUNCTION_IMPORT, 'function']);
$items[] = new Token([T_WHITESPACE, ' ']);
}

$items = array_merge($items, self::tokenizeName($name));
$items[] = new Token(';');
}

$tokens->insertAt($atIndex, $items);
}
}






public static function tokenizeName(string $name): array
{
$parts = explode('\\', $name);
$newTokens = [];

if ('' === $parts[0]) {
$newTokens[] = new Token([T_NS_SEPARATOR, '\\']);
array_shift($parts);
}

foreach ($parts as $part) {
$newTokens[] = new Token([T_STRING, $part]);
$newTokens[] = new Token([T_NS_SEPARATOR, '\\']);
}

array_pop($newTokens);

return $newTokens;
}
}
