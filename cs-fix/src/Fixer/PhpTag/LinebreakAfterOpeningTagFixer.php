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




final class LinebreakAfterOpeningTagFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Ensure there is no code on the same line as the PHP open tag.',
[new CodeSample("<?php \$a = 1;\n\$b = 3;\n")]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isMonolithicPhp() && !$tokens->isTokenKindFound(T_OPEN_TAG_WITH_ECHO);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$openTagIndex = $tokens[0]->isGivenKind(T_INLINE_HTML) ? 1 : 0;


if (str_contains($tokens[$openTagIndex]->getContent(), "\n")) {
return;
}

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

$tokens[$openTagIndex] = new Token([T_OPEN_TAG, rtrim($tokens[$openTagIndex]->getContent()).$this->whitespacesConfig->getLineEnding()]);
}
}
