<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Comment;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class SingleLineCommentSpacingFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Single-line comments must have proper spacing.',
[
new CodeSample(
'<?php
//comment 1
#comment 2
/*comment 3*/
'
),
]
);
}






public function getPriority(): int
{
return 1;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_COMMENT);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = \count($tokens) - 1; 0 <= $index; --$index) {
$token = $tokens[$index];

if (!$token->isGivenKind(T_COMMENT)) {
continue;
}

$content = $token->getContent();
$contentLength = \strlen($content);

if ('/' === $content[0]) {
if ($contentLength < 3) {
continue; 
}

if ('*' === $content[1]) { 
if ($contentLength < 5 || '*' === $content[2] || str_contains($content, "\n")) {
continue; 
}

$newContent = rtrim(substr($content, 0, -2)).' '.substr($content, -2);
$newContent = $this->fixCommentLeadingSpace($newContent, '/*');
} else { 
$newContent = $this->fixCommentLeadingSpace($content, '//');
}
} else { 
if ($contentLength < 2 || '[' === $content[1]) { 
continue;
}

$newContent = $this->fixCommentLeadingSpace($content, '#');
}

if ($newContent !== $content) {
$tokens[$index] = new Token([T_COMMENT, $newContent]);
}
}
}


private function fixCommentLeadingSpace(string $content, string $prefix): string
{
if (Preg::match(\sprintf('@^%s\h+.*$@', preg_quote($prefix, '@')), $content)) {
return $content;
}

$position = \strlen($prefix);

return substr($content, 0, $position).' '.substr($content, $position);
}
}
