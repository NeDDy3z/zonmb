<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class SingleTraitInsertPerStatementFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Each trait `use` must be done as single statement.',
[
new CodeSample(
'<?php
final class Example
{
    use Foo, Bar;
}
'
),
]
);
}






public function getPriority(): int
{
return 36;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(CT::T_USE_TRAIT);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = \count($tokens) - 1; 1 < $index; --$index) {
if ($tokens[$index]->isGivenKind(CT::T_USE_TRAIT)) {
$candidates = $this->getCandidates($tokens, $index);
if (\count($candidates) > 0) {
$this->fixTraitUse($tokens, $index, $candidates);
}
}
}
}




private function fixTraitUse(Tokens $tokens, int $useTraitIndex, array $candidates): void
{
foreach ($candidates as $commaIndex) {
$inserts = [
new Token([CT::T_USE_TRAIT, 'use']),
new Token([T_WHITESPACE, ' ']),
];

$nextImportStartIndex = $tokens->getNextMeaningfulToken($commaIndex);

if ($tokens[$nextImportStartIndex - 1]->isWhitespace()) {
if (Preg::match('/\R/', $tokens[$nextImportStartIndex - 1]->getContent())) {
array_unshift($inserts, clone $tokens[$useTraitIndex - 1]);
}
$tokens->clearAt($nextImportStartIndex - 1);
}

$tokens[$commaIndex] = new Token(';');
$tokens->insertAt($nextImportStartIndex, $inserts);
}
}




private function getCandidates(Tokens $tokens, int $index): array
{
$indices = [];
$index = $tokens->getNextTokenOfKind($index, [',', ';', '{']);

while (!$tokens[$index]->equals(';')) {
if ($tokens[$index]->equals('{')) {
return []; 
}

$indices[] = $index;
$index = $tokens->getNextTokenOfKind($index, [',', ';', '{']);
}

return array_reverse($indices);
}
}
