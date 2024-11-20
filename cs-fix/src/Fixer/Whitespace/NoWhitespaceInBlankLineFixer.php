<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Tokens;




final class NoWhitespaceInBlankLineFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Remove trailing whitespace at the end of blank lines.',
[new CodeSample("<?php\n   \n\$a = 1;\n")]
);
}






public function getPriority(): int
{
return -99;
}

public function isCandidate(Tokens $tokens): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{

for ($i = 1, $count = \count($tokens); $i < $count; ++$i) {
if ($tokens[$i]->isWhitespace()) {
$this->fixWhitespaceToken($tokens, $i);
}
}
}

private function fixWhitespaceToken(Tokens $tokens, int $index): void
{
$content = $tokens[$index]->getContent();
$lines = Preg::split("/(\r\n|\n)/", $content);
$lineCount = \count($lines);

if (

$lineCount > 2

|| ($lineCount > 0 && (!isset($tokens[$index + 1]) || $tokens[$index - 1]->isGivenKind(T_OPEN_TAG)))
) {
$lMax = isset($tokens[$index + 1]) ? $lineCount - 1 : $lineCount;

$lStart = 1;
if ($tokens[$index - 1]->isGivenKind(T_OPEN_TAG) && "\n" === substr($tokens[$index - 1]->getContent(), -1)) {
$lStart = 0;
}

for ($l = $lStart; $l < $lMax; ++$l) {
$lines[$l] = Preg::replace('/^\h+$/', '', $lines[$l]);
}
$content = implode($this->whitespacesConfig->getLineEnding(), $lines);
$tokens->ensureWhitespaceAtIndex($index, 0, $content);
}
}
}
