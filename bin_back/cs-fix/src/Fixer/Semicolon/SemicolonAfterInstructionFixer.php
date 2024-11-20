<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Semicolon;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class SemicolonAfterInstructionFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Instructions must be terminated with a semicolon.',
[new CodeSample("<?php echo 1 ?>\n")]
);
}






public function getPriority(): int
{
return 2;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_CLOSE_TAG);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = \count($tokens) - 1; $index > 1; --$index) {
if (!$tokens[$index]->isGivenKind(T_CLOSE_TAG)) {
continue;
}

$prev = $tokens->getPrevMeaningfulToken($index);
if ($tokens[$prev]->equalsAny([';', '{', '}', ':', [T_OPEN_TAG]])) {
continue;
}

$tokens->insertAt($prev + 1, new Token(';'));
}
}
}
