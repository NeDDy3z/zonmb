<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ArrayNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;






final class NoMultilineWhitespaceAroundDoubleArrowFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Operator `=>` should not be surrounded by multi-line whitespaces.',
[new CodeSample("<?php\n\$a = array(1\n\n=> 2);\n")]
);
}






public function getPriority(): int
{
return 31;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOUBLE_ARROW);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_DOUBLE_ARROW)) {
continue;
}

if (!$tokens[$index - 2]->isComment() || str_starts_with($tokens[$index - 2]->getContent(), '/*')) {
$this->fixWhitespace($tokens, $index - 1);
}


if (!$tokens[$index + 2]->isComment()) {
$this->fixWhitespace($tokens, $index + 1);
}
}
}

private function fixWhitespace(Tokens $tokens, int $index): void
{
$token = $tokens[$index];

if ($token->isWhitespace() && !$token->isWhitespace(" \t")) {
$tokens[$index] = new Token([T_WHITESPACE, rtrim($token->getContent()).' ']);
}
}
}
