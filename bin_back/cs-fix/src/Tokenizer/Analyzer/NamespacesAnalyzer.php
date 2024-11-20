<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Tokens;




final class NamespacesAnalyzer
{



public function getDeclarations(Tokens $tokens): array
{
$namespaces = [];

for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
$token = $tokens[$index];

if (!$token->isGivenKind(T_NAMESPACE)) {
continue;
}

$declarationEndIndex = $tokens->getNextTokenOfKind($index, [';', '{']);
$namespace = trim($tokens->generatePartialCode($index + 1, $declarationEndIndex - 1));
$declarationParts = explode('\\', $namespace);
$shortName = end($declarationParts);

if ($tokens[$declarationEndIndex]->equals('{')) {
$scopeEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $declarationEndIndex);
} else {
$scopeEndIndex = $tokens->getNextTokenOfKind($declarationEndIndex, [[T_NAMESPACE]]);
if (null === $scopeEndIndex) {
$scopeEndIndex = \count($tokens);
}
--$scopeEndIndex;
}

$namespaces[] = new NamespaceAnalysis(
$namespace,
$shortName,
$index,
$declarationEndIndex,
$index,
$scopeEndIndex
);


$index = $scopeEndIndex;
}

if (0 === \count($namespaces) && $tokens->isTokenKindFound(T_OPEN_TAG)) {
$namespaces[] = new NamespaceAnalysis(
'',
'',
$openTagIndex = $tokens[0]->isGivenKind(T_INLINE_HTML) ? 1 : 0,
$openTagIndex,
$openTagIndex,
\count($tokens) - 1,
);
}

return $namespaces;
}

public function getNamespaceAt(Tokens $tokens, int $index): NamespaceAnalysis
{
if (!$tokens->offsetExists($index)) {
throw new \InvalidArgumentException(\sprintf('Token index %d does not exist.', $index));
}

foreach ($this->getDeclarations($tokens) as $namespace) {
if ($namespace->getScopeStartIndex() <= $index && $namespace->getScopeEndIndex() >= $index) {
return $namespace;
}
}

throw new \LogicException(\sprintf('Unable to get the namespace at index %d.', $index));
}
}
