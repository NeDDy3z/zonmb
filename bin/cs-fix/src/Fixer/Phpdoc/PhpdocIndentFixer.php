<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Utils;





final class PhpdocIndentFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Docblocks should have the same indentation as the documented subject.',
[new CodeSample('<?php
class DocBlocks
{
/**
 * Test constants
 */
    const INDENT = 1;
}
')]
);
}







public function getPriority(): int
{
return 20;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
$token = $tokens[$index];

if (!$token->isGivenKind(T_DOC_COMMENT)) {
continue;
}

$nextIndex = $tokens->getNextMeaningfulToken($index);


if (null === $nextIndex || $tokens[$nextIndex]->equals('}')) {
continue;
}

$prevIndex = $index - 1;
$prevToken = $tokens[$prevIndex];


if (
$prevToken->isGivenKind(T_OPEN_TAG)
|| ($prevToken->isWhitespace(" \t") && !$tokens[$index - 2]->isGivenKind(T_OPEN_TAG))
|| $prevToken->equalsAny([';', ',', '{', '('])
) {
continue;
}

if ($tokens[$nextIndex - 1]->isWhitespace()) {
$indent = Utils::calculateTrailingWhitespaceIndent($tokens[$nextIndex - 1]);
} else {
$indent = '';
}

$newPrevContent = $this->fixWhitespaceBeforeDocblock($prevToken->getContent(), $indent);

$tokens[$index] = new Token([T_DOC_COMMENT, $this->fixDocBlock($token->getContent(), $indent)]);

if (!$prevToken->isWhitespace()) {
if ('' !== $indent) {
$tokens->insertAt($index, new Token([T_WHITESPACE, $indent]));
}
} elseif ('' !== $newPrevContent) {
if ($prevToken->isArray()) {
$tokens[$prevIndex] = new Token([$prevToken->getId(), $newPrevContent]);
} else {
$tokens[$prevIndex] = new Token($newPrevContent);
}
} else {
$tokens->clearAt($prevIndex);
}
}
}









private function fixDocBlock(string $content, string $indent): string
{
return ltrim(Preg::replace('/^\h*\*/m', $indent.' *', $content));
}







private function fixWhitespaceBeforeDocblock(string $content, string $indent): string
{
return rtrim($content, " \t").$indent;
}
}
