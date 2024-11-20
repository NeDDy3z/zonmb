<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class NoUselessNullsafeOperatorFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'There should not be useless Null-safe operator `?->` used.',
[
new VersionSpecificCodeSample(
'<?php
class Foo extends Bar
{
    public function test() {
        echo $this?->parentMethod();
    }
}
',
new VersionSpecification(8_00_00)
),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return \PHP_VERSION_ID >= 8_00_00 && $tokens->isAllTokenKindsFound([T_VARIABLE, T_NULLSAFE_OBJECT_OPERATOR]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = $tokens->count() - 1; $index >= 0; --$index) {
if (!$tokens[$index]->isGivenKind(T_NULLSAFE_OBJECT_OPERATOR)) {
continue;
}

$nullsafeObjectOperatorIndex = $index;
$index = $tokens->getPrevMeaningfulToken($index);

if (!$tokens[$index]->isGivenKind(T_VARIABLE)) {
continue;
}

if ('$this' !== strtolower($tokens[$index]->getContent())) {
continue;
}

$tokens[$nullsafeObjectOperatorIndex] = new Token([T_OBJECT_OPERATOR, '->']);
}
}
}
