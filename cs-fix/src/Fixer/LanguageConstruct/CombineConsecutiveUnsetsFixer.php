<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\LanguageConstruct;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class CombineConsecutiveUnsetsFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Calling `unset` on multiple items should be done in one call.',
[new CodeSample("<?php\nunset(\$a); unset(\$b);\n")]
);
}







public function getPriority(): int
{
return 24;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_UNSET);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = $tokens->count() - 1; $index >= 0; --$index) {
if (!$tokens[$index]->isGivenKind(T_UNSET)) {
continue;
}

$previousUnsetCall = $this->getPreviousUnsetCall($tokens, $index);
if (\is_int($previousUnsetCall)) {
$index = $previousUnsetCall;

continue;
}

[$previousUnset, , $previousUnsetBraceEnd] = $previousUnsetCall;


$tokensAddCount = $this->moveTokens(
$tokens,
$nextUnsetContentStart = $tokens->getNextTokenOfKind($index, ['(']),
$nextUnsetContentEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $nextUnsetContentStart),
$previousUnsetBraceEnd - 1
);

if (!$tokens[$previousUnsetBraceEnd]->isWhitespace()) {
$tokens->insertAt($previousUnsetBraceEnd, new Token([T_WHITESPACE, ' ']));
++$tokensAddCount;
}

$tokens->insertAt($previousUnsetBraceEnd, new Token(','));
++$tokensAddCount;


$this->clearOffsetTokens($tokens, $tokensAddCount, [$index, $nextUnsetContentStart, $nextUnsetContentEnd]);

$nextUnsetSemicolon = $tokens->getNextMeaningfulToken($nextUnsetContentEnd);
if (null !== $nextUnsetSemicolon && $tokens[$nextUnsetSemicolon]->equals(';')) {
$tokens->clearTokenAndMergeSurroundingWhitespace($nextUnsetSemicolon);
}

$index = $previousUnset + 1;
}
}




private function clearOffsetTokens(Tokens $tokens, int $offset, array $indices): void
{
foreach ($indices as $index) {
$tokens->clearTokenAndMergeSurroundingWhitespace($index + $offset);
}
}














private function getPreviousUnsetCall(Tokens $tokens, int $index)
{
$previousUnsetSemicolon = $tokens->getPrevMeaningfulToken($index);
if (null === $previousUnsetSemicolon) {
return $index;
}

if (!$tokens[$previousUnsetSemicolon]->equals(';')) {
return $previousUnsetSemicolon;
}

$previousUnsetBraceEnd = $tokens->getPrevMeaningfulToken($previousUnsetSemicolon);
if (null === $previousUnsetBraceEnd) {
return $index;
}

if (!$tokens[$previousUnsetBraceEnd]->equals(')')) {
return $previousUnsetBraceEnd;
}

$previousUnsetBraceStart = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $previousUnsetBraceEnd);
$previousUnset = $tokens->getPrevMeaningfulToken($previousUnsetBraceStart);
if (null === $previousUnset) {
return $index;
}

if (!$tokens[$previousUnset]->isGivenKind(T_UNSET)) {
return $previousUnset;
}

return [
$previousUnset,
$previousUnsetBraceStart,
$previousUnsetBraceEnd,
$previousUnsetSemicolon,
];
}








private function moveTokens(Tokens $tokens, int $start, int $end, int $to): int
{
$added = 0;
for ($i = $start + 1; $i < $end; $i += 2) {
if ($tokens[$i]->isWhitespace() && $tokens[$to + 1]->isWhitespace()) {
$tokens[$to + 1] = new Token([T_WHITESPACE, $tokens[$to + 1]->getContent().$tokens[$i]->getContent()]);
} else {
$tokens->insertAt(++$to, clone $tokens[$i]);
++$end;
++$added;
}

$tokens->clearAt($i + 1);
}

return $added;
}
}
