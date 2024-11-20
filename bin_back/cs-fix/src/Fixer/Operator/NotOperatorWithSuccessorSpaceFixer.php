<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;




final class NotOperatorWithSuccessorSpaceFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Logical NOT operators (`!`) should have one trailing whitespace.',
[new CodeSample(
'<?php

if (!$bar) {
    echo "Help!";
}
'
)]
);
}






public function getPriority(): int
{
return -10;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound('!');
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = $tokens->count() - 1; $index >= 0; --$index) {
$token = $tokens[$index];

if ($token->equals('!')) {
$tokens->ensureWhitespaceAtIndex($index + 1, 0, ' ');
}
}
}
}
