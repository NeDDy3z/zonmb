<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;





final class ObjectOperatorWithoutWhitespaceFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'There should not be space before or after object operators `->` and `?->`.',
[new CodeSample("<?php \$a  ->  b;\n")]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound(Token::getObjectOperatorKinds());
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{

foreach ($tokens as $index => $token) {
if (!$token->isObjectOperator()) {
continue;
}


if ($tokens[$index - 1]->isWhitespace(" \t") && !$tokens[$index - 2]->isComment()) {
$tokens->clearAt($index - 1);
}


if ($tokens[$index + 1]->isWhitespace(" \t") && !$tokens[$index + 2]->isComment()) {
$tokens->clearAt($index + 1);
}
}
}
}
