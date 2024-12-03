<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Strict;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class StrictComparisonFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Comparisons should be strict.',
[new CodeSample("<?php\n\$a = 1== \$b;\n")],
null,
'Changing comparisons to strict might change code behavior.'
);
}






public function getPriority(): int
{
return 38;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound([T_IS_EQUAL, T_IS_NOT_EQUAL]);
}

public function isRisky(): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
static $map = [
T_IS_EQUAL => [
'id' => T_IS_IDENTICAL,
'content' => '===',
],
T_IS_NOT_EQUAL => [
'id' => T_IS_NOT_IDENTICAL,
'content' => '!==',
],
];

foreach ($tokens as $index => $token) {
$tokenId = $token->getId();

if (isset($map[$tokenId])) {
$tokens[$index] = new Token([$map[$tokenId]['id'], $map[$tokenId]['content']]);
}
}
}
}
