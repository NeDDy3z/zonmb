<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Import;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use PhpCsFixer\Utils;







final class SingleLineAfterImportsFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_USE);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Each namespace use MUST go on its own line and there MUST be one blank line after the use statements block.',
[
new CodeSample(
'<?php
namespace Foo;

use Bar;
use Baz;
final class Example
{
}
'
),
new CodeSample(
'<?php
namespace Foo;

use Bar;
use Baz;


final class Example
{
}
'
),
]
);
}






public function getPriority(): int
{
return -11;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$ending = $this->whitespacesConfig->getLineEnding();
$tokensAnalyzer = new TokensAnalyzer($tokens);

$added = 0;
foreach ($tokensAnalyzer->getImportUseIndexes() as $index) {
$index += $added;
$indent = '';


if ($tokens[$index - 1]->isWhitespace(" \t") && $tokens[$index - 2]->isGivenKind(T_COMMENT)) {
$indent = $tokens[$index - 1]->getContent();
} elseif ($tokens[$index - 1]->isWhitespace()) {
$indent = Utils::calculateTrailingWhitespaceIndent($tokens[$index - 1]);
}

$semicolonIndex = $tokens->getNextTokenOfKind($index, [';', [T_CLOSE_TAG]]); 
$insertIndex = $semicolonIndex;

if ($tokens[$semicolonIndex]->isGivenKind(T_CLOSE_TAG)) {
if ($tokens[$insertIndex - 1]->isWhitespace()) {
--$insertIndex;
}

$tokens->insertAt($insertIndex, new Token(';'));
++$added;
}

if ($semicolonIndex === \count($tokens) - 1) {
$tokens->insertAt($insertIndex + 1, new Token([T_WHITESPACE, $ending.$ending.$indent]));
++$added;
} else {
$newline = $ending;
$tokens[$semicolonIndex]->isGivenKind(T_CLOSE_TAG) ? --$insertIndex : ++$insertIndex;
if ($tokens[$insertIndex]->isWhitespace(" \t") && $tokens[$insertIndex + 1]->isComment()) {
++$insertIndex;
}


if ($tokens[$insertIndex]->isComment()) {
++$insertIndex;
}

$afterSemicolon = $tokens->getNextMeaningfulToken($semicolonIndex);
if (null === $afterSemicolon || !$tokens[$afterSemicolon]->isGivenKind(T_USE)) {
$newline .= $ending;
}

if ($tokens[$insertIndex]->isWhitespace()) {
$nextToken = $tokens[$insertIndex];
if (2 === substr_count($nextToken->getContent(), "\n")) {
continue;
}
$nextMeaningfulAfterUseIndex = $tokens->getNextMeaningfulToken($insertIndex);
if (null !== $nextMeaningfulAfterUseIndex && $tokens[$nextMeaningfulAfterUseIndex]->isGivenKind(T_USE)) {
if (substr_count($nextToken->getContent(), "\n") < 1) {
$tokens[$insertIndex] = new Token([T_WHITESPACE, $newline.$indent.ltrim($nextToken->getContent())]);
}
} else {
$tokens[$insertIndex] = new Token([T_WHITESPACE, $newline.$indent.ltrim($nextToken->getContent())]);
}
} else {
$tokens->insertAt($insertIndex, new Token([T_WHITESPACE, $newline.$indent]));
++$added;
}
}
}
}
}
