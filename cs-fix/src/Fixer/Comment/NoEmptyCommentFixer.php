<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Comment;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Tokens;

final class NoEmptyCommentFixer extends AbstractFixer
{
private const TYPE_HASH = 1;

private const TYPE_DOUBLE_SLASH = 2;

private const TYPE_SLASH_ASTERISK = 3;







public function getPriority(): int
{
return 2;
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'There should not be any empty comments.',
[new CodeSample("<?php\n//\n#\n/* */\n")]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_COMMENT);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
if (!$tokens[$index]->isGivenKind(T_COMMENT)) {
continue;
}

$blockInfo = $this->getCommentBlock($tokens, $index);
$blockStart = $blockInfo['blockStart'];
$index = $blockInfo['blockEnd'];
$isEmpty = $blockInfo['isEmpty'];

if (false === $isEmpty) {
continue;
}

for ($i = $blockStart; $i <= $index; ++$i) {
$tokens->clearTokenAndMergeSurroundingWhitespace($i);
}
}
}








private function getCommentBlock(Tokens $tokens, int $index): array
{
$commentType = $this->getCommentType($tokens[$index]->getContent());
$empty = $this->isEmptyComment($tokens[$index]->getContent());

if (self::TYPE_SLASH_ASTERISK === $commentType) {
return [
'blockStart' => $index,
'blockEnd' => $index,
'isEmpty' => $empty,
];
}

$start = $index;
$count = \count($tokens);
++$index;

for (; $index < $count; ++$index) {
if ($tokens[$index]->isComment()) {
if ($commentType !== $this->getCommentType($tokens[$index]->getContent())) {
break;
}

if ($empty) { 
$empty = $this->isEmptyComment($tokens[$index]->getContent());
}

continue;
}

if (!$tokens[$index]->isWhitespace() || $this->getLineBreakCount($tokens, $index, $index + 1) > 1) {
break;
}
}

return [
'blockStart' => $start,
'blockEnd' => $index - 1,
'isEmpty' => $empty,
];
}

private function getCommentType(string $content): int
{
if (str_starts_with($content, '#')) {
return self::TYPE_HASH;
}

if ('*' === $content[1]) {
return self::TYPE_SLASH_ASTERISK;
}

return self::TYPE_DOUBLE_SLASH;
}

private function getLineBreakCount(Tokens $tokens, int $whiteStart, int $whiteEnd): int
{
$lineCount = 0;
for ($i = $whiteStart; $i < $whiteEnd; ++$i) {
$lineCount += Preg::matchAll('/\R/u', $tokens[$i]->getContent(), $matches);
}

return $lineCount;
}

private function isEmptyComment(string $content): bool
{
static $mapper = [
self::TYPE_HASH => '|^#\s*$|', 
self::TYPE_SLASH_ASTERISK => '|^/\*[\s\*]*\*+/$|', 
self::TYPE_DOUBLE_SLASH => '|^//\s*$|', 
];

$type = $this->getCommentType($content);

return Preg::match($mapper[$type], $content);
}
}
