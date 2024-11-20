<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\AlternativeSyntaxAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\GotoLabelAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\SwitchAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class TernaryOperatorSpacesFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Standardize spaces around ternary operator.',
[new CodeSample("<?php \$a = \$a   ?1 :0;\n")]
);
}






public function getPriority(): int
{
return 1;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound(['?', ':']);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$alternativeSyntaxAnalyzer = new AlternativeSyntaxAnalyzer();
$gotoLabelAnalyzer = new GotoLabelAnalyzer();
$ternaryOperatorIndices = [];

foreach ($tokens as $index => $token) {
if (!$token->equalsAny(['?', ':'])) {
continue;
}

if (SwitchAnalyzer::belongsToSwitch($tokens, $index)) {
continue;
}

if ($alternativeSyntaxAnalyzer->belongsToAlternativeSyntax($tokens, $index)) {
continue;
}

if ($gotoLabelAnalyzer->belongsToGoToLabel($tokens, $index)) {
continue;
}

$ternaryOperatorIndices[] = $index;
}

foreach (array_reverse($ternaryOperatorIndices) as $index) {
$token = $tokens[$index];

if ($token->equals('?')) {
$nextNonWhitespaceIndex = $tokens->getNextNonWhitespace($index);

if ($tokens[$nextNonWhitespaceIndex]->equals(':')) {

$tokens->ensureWhitespaceAtIndex($index + 1, 0, '');
} else {

$this->ensureWhitespaceExistence($tokens, $index + 1, true);
}


$this->ensureWhitespaceExistence($tokens, $index - 1, false);

continue;
}

if ($token->equals(':')) {

$this->ensureWhitespaceExistence($tokens, $index + 1, true);

$prevNonWhitespaceToken = $tokens[$tokens->getPrevNonWhitespace($index)];

if (!$prevNonWhitespaceToken->equals('?')) {

$this->ensureWhitespaceExistence($tokens, $index - 1, false);
}
}
}
}

private function ensureWhitespaceExistence(Tokens $tokens, int $index, bool $after): void
{
if ($tokens[$index]->isWhitespace()) {
if (
!str_contains($tokens[$index]->getContent(), "\n")
&& !$tokens[$index - 1]->isComment()
) {
$tokens[$index] = new Token([T_WHITESPACE, ' ']);
}

return;
}

$index += $after ? 0 : 1;
$tokens->insertAt($index, new Token([T_WHITESPACE, ' ']));
}
}
