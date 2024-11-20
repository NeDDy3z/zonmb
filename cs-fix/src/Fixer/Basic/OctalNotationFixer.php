<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Basic;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class OctalNotationFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Literal octal must be in `0o` notation.',
[
new VersionSpecificCodeSample(
"<?php \$foo = 0123;\n",
new VersionSpecification(8_01_00)
),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return \PHP_VERSION_ID >= 8_01_00 && $tokens->isTokenKindFound(T_LNUMBER);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_LNUMBER)) {
continue;
}

$content = $token->getContent();

$newContent = Preg::replace('#^0_*+([0-7_]+)$#', '0o$1', $content);

if ($content === $newContent) {
continue;
}

$tokens[$index] = new Token([T_LNUMBER, $newContent]);
}
}
}
