<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class LogicalOperatorsFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Use `&&` and `||` logical operators instead of `and` and `or`.',
[
new CodeSample(
'<?php

if ($a == "foo" and ($b == "bar" or $c == "baz")) {
}
'
),
],
null,
'Risky, because you must double-check if using and/or with lower precedence was intentional.'
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound([T_LOGICAL_AND, T_LOGICAL_OR]);
}

public function isRisky(): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if ($token->isGivenKind(T_LOGICAL_AND)) {
$tokens[$index] = new Token([T_BOOLEAN_AND, '&&']);
} elseif ($token->isGivenKind(T_LOGICAL_OR)) {
$tokens[$index] = new Token([T_BOOLEAN_OR, '||']);
}
}
}
}
