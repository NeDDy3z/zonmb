<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\Fixer\AbstractShortOperatorFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class AssignNullCoalescingToCoalesceEqualFixer extends AbstractShortOperatorFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Use the null coalescing assignment operator `??=` where possible.',
[
new CodeSample(
"<?php\n\$foo = \$foo ?? 1;\n",
),
]
);
}







public function getPriority(): int
{
return -1;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_COALESCE);
}

protected function isOperatorTokenCandidate(Tokens $tokens, int $index): bool
{
if (!$tokens[$index]->isGivenKind(T_COALESCE)) {
return false;
}



$nextIndex = $tokens->getNextTokenOfKind($index, ['?', ';', [T_CLOSE_TAG]]);

return !$tokens[$nextIndex]->equals('?');
}

protected function getReplacementToken(Token $token): Token
{
return new Token([T_COALESCE_EQUAL, '??=']);
}
}
