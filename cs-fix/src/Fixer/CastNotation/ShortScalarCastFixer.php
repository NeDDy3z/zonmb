<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\CastNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class ShortScalarCastFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Cast `(boolean)` and `(integer)` should be written as `(bool)` and `(int)`, `(double)` and `(real)` as `(float)`, `(binary)` as `(string)`.',
[
new CodeSample(
"<?php\n\$a = (boolean) \$b;\n\$a = (integer) \$b;\n\$a = (double) \$b;\n\n\$a = (binary) \$b;\n",
),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound(Token::getCastTokenKinds());
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
static $castMap = [
'boolean' => 'bool',
'integer' => 'int',
'double' => 'float',
'real' => 'float',
'binary' => 'string',
];

for ($index = 0, $count = $tokens->count(); $index < $count; ++$index) {
if (!$tokens[$index]->isCast()) {
continue;
}

$castFrom = trim(substr($tokens[$index]->getContent(), 1, -1));
$castFromLowered = strtolower($castFrom);

if (!\array_key_exists($castFromLowered, $castMap)) {
continue;
}

$tokens[$index] = new Token([
$tokens[$index]->getId(),
str_replace($castFrom, $castMap[$castFromLowered], $tokens[$index]->getContent()),
]);
}
}
}
