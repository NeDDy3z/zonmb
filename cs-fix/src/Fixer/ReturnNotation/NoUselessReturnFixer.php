<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ReturnNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

final class NoUselessReturnFixer extends AbstractFixer
{
public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound([T_FUNCTION, T_RETURN]);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'There should not be an empty `return` statement at the end of a function.',
[
new CodeSample(
'<?php
function example($b) {
    if ($b) {
        return;
    }
    return;
}
'
),
]
);
}







public function getPriority(): int
{
return -18;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_FUNCTION)) {
continue;
}

$index = $tokens->getNextTokenOfKind($index, [';', '{']);
if ($tokens[$index]->equals('{')) {
$this->fixFunction($tokens, $index, $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $index));
}
}
}





private function fixFunction(Tokens $tokens, int $start, int $end): void
{
for ($index = $end; $index > $start; --$index) {
if (!$tokens[$index]->isGivenKind(T_RETURN)) {
continue;
}

$nextAt = $tokens->getNextMeaningfulToken($index);
if (!$tokens[$nextAt]->equals(';')) {
continue;
}

if ($tokens->getNextMeaningfulToken($nextAt) !== $end) {
continue;
}

$previous = $tokens->getPrevMeaningfulToken($index);
if ($tokens[$previous]->equalsAny([[T_ELSE], ')'])) {
continue;
}

$tokens->clearTokenAndMergeSurroundingWhitespace($index);
$tokens->clearTokenAndMergeSurroundingWhitespace($nextAt);
}
}
}
