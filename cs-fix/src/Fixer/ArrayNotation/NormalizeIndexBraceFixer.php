<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ArrayNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class NormalizeIndexBraceFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Array index should always be written by using square braces.',
[new VersionSpecificCodeSample(
"<?php\necho \$sample{\$index};\n",
new VersionSpecification(null, 8_04_00 - 1)
)]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(CT::T_ARRAY_INDEX_CURLY_BRACE_OPEN);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if ($token->isGivenKind(CT::T_ARRAY_INDEX_CURLY_BRACE_OPEN)) {
$tokens[$index] = new Token('[');
} elseif ($token->isGivenKind(CT::T_ARRAY_INDEX_CURLY_BRACE_CLOSE)) {
$tokens[$index] = new Token(']');
}
}
}
}
