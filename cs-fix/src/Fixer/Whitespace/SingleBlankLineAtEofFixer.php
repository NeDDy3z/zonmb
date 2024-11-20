<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;









final class SingleBlankLineAtEofFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'A PHP file without end tag must always end with a single empty line feed.',
[
new CodeSample("<?php\n\$a = 1;"),
new CodeSample("<?php\n\$a = 1;\n\n"),
]
);
}

public function getPriority(): int
{

return -100;
}

public function isCandidate(Tokens $tokens): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$count = $tokens->count();

if ($count > 0 && !$tokens[$count - 1]->isGivenKind([T_INLINE_HTML, T_CLOSE_TAG, T_OPEN_TAG])) {
$tokens->ensureWhitespaceAtIndex($count - 1, 1, $this->whitespacesConfig->getLineEnding());
}
}
}
