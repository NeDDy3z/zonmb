<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;




final class CompactNullableTypeDeclarationFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Remove extra spaces in a nullable type declaration.',
[
new CodeSample(
"<?php\nfunction sample(? string \$str): ? string\n{}\n"
),
],
'Rule is applied only in a PHP 7.1+ environment.'
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(CT::T_NULLABLE_TYPE);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
static $typehintKinds = [
CT::T_ARRAY_TYPEHINT,
T_CALLABLE,
T_NS_SEPARATOR,
T_STATIC,
T_STRING,
];

for ($index = $tokens->count() - 1; $index >= 0; --$index) {
if (!$tokens[$index]->isGivenKind(CT::T_NULLABLE_TYPE)) {
continue;
}



if (
$tokens[$index + 1]->isWhitespace()
&& $tokens[$index + 2]->isGivenKind($typehintKinds)
) {
$tokens->removeTrailingWhitespace($index);
}
}
}
}
