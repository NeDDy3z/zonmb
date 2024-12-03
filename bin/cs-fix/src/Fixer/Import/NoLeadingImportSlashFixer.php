<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Import;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;




final class NoLeadingImportSlashFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Remove leading slashes in `use` clauses.',
[new CodeSample("<?php\nnamespace Foo;\nuse \\Bar;\n")]
);
}







public function getPriority(): int
{
return -20;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_USE);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$tokensAnalyzer = new TokensAnalyzer($tokens);
$usesIndices = $tokensAnalyzer->getImportUseIndexes();

foreach ($usesIndices as $idx) {
$nextTokenIdx = $tokens->getNextMeaningfulToken($idx);
$nextToken = $tokens[$nextTokenIdx];

if ($nextToken->isGivenKind(T_NS_SEPARATOR)) {
$this->removeLeadingImportSlash($tokens, $nextTokenIdx);
} elseif ($nextToken->isGivenKind([CT::T_FUNCTION_IMPORT, CT::T_CONST_IMPORT])) {
$nextTokenIdx = $tokens->getNextMeaningfulToken($nextTokenIdx);
if ($tokens[$nextTokenIdx]->isGivenKind(T_NS_SEPARATOR)) {
$this->removeLeadingImportSlash($tokens, $nextTokenIdx);
}
}
}
}

private function removeLeadingImportSlash(Tokens $tokens, int $index): void
{
$previousIndex = $tokens->getPrevNonWhitespace($index);

if (
$previousIndex < $index - 1
|| $tokens[$previousIndex]->isComment()
) {
$tokens->clearAt($index);

return;
}

$tokens[$index] = new Token([T_WHITESPACE, ' ']);
}
}
