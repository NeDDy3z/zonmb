<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class StandardizeNotEqualsFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Replace all `<>` with `!=`.',
[new CodeSample("<?php\n\$a = \$b <> \$c;\n")]
);
}






public function getPriority(): int
{
return 0;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_IS_NOT_EQUAL);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if ($token->isGivenKind(T_IS_NOT_EQUAL)) {
$tokens[$index] = new Token([T_IS_NOT_EQUAL, '!=']);
}
}
}
}
