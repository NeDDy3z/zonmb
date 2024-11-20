<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Semicolon;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;




final class NoSinglelineWhitespaceBeforeSemicolonsFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Single-line whitespace before closing semicolon are prohibited.',
[new CodeSample("<?php \$this->foo() ;\n")]
);
}






public function getPriority(): int
{
return 0;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(';');
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->equals(';') || !$tokens[$index - 1]->isWhitespace(" \t")) {
continue;
}

if ($tokens[$index - 2]->equals(';')) {

$tokens->ensureWhitespaceAtIndex($index - 1, 0, ' ');
} elseif (!$tokens[$index - 2]->isComment()) {
$tokens->clearAt($index - 1);
}
}
}
}
