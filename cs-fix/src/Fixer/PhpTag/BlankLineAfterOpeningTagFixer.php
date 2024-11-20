<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\PhpTag;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class BlankLineAfterOpeningTagFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Ensure there is no code on the same line as the PHP open tag and it is followed by a blank line.',
[new CodeSample("<?php \$a = 1;\n\$b = 1;\n")]
);
}







public function getPriority(): int
{
return 1;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isMonolithicPhp() && !$tokens->isTokenKindFound(T_OPEN_TAG_WITH_ECHO);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$lineEnding = $this->whitespacesConfig->getLineEnding();

$newlineFound = false;
foreach ($tokens as $token) {
if (($token->isWhitespace() || $token->isGivenKind(T_OPEN_TAG)) && str_contains($token->getContent(), "\n")) {
$newlineFound = true;

break;
}
}


if (!$newlineFound) {
return;
}

$openTagIndex = $tokens[0]->isGivenKind(T_INLINE_HTML) ? 1 : 0;
$token = $tokens[$openTagIndex];

if (!str_contains($token->getContent(), "\n")) {
$tokens[$openTagIndex] = new Token([$token->getId(), rtrim($token->getContent()).$lineEnding]);
}

$newLineIndex = $openTagIndex + 1;
if (isset($tokens[$newLineIndex]) && !str_contains($tokens[$newLineIndex]->getContent(), "\n")) {
if ($tokens[$newLineIndex]->isWhitespace()) {
$tokens[$newLineIndex] = new Token([T_WHITESPACE, $lineEnding.$tokens[$newLineIndex]->getContent()]);
} else {
$tokens->insertAt($newLineIndex, new Token([T_WHITESPACE, $lineEnding]));
}
}
}
}
