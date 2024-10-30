<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\StringNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class NoBinaryStringFixer extends AbstractFixer
{
public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound(
[
T_CONSTANT_ENCAPSED_STRING,
T_START_HEREDOC,
'b"',
]
);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'There should not be a binary flag before strings.',
[
new CodeSample("<?php \$a = b'foo';\n"),
new CodeSample("<?php \$a = b<<<EOT\nfoo\nEOT;\n"),
]
);
}






public function getPriority(): int
{
return 40;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if ($token->isGivenKind([T_CONSTANT_ENCAPSED_STRING, T_START_HEREDOC])) {
$content = $token->getContent();

if ('b' === strtolower($content[0])) {
$tokens[$index] = new Token([$token->getId(), substr($content, 1)]);
}
} elseif ($token->equals('b"')) {
$tokens[$index] = new Token('"');
}
}
}
}
