<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractNoUselessElseFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

final class NoUselessElseFixer extends AbstractNoUselessElseFixer
{
public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_ELSE);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'There should not be useless `else` cases.',
[
new CodeSample("<?php\nif (\$a) {\n    return 1;\n} else {\n    return 2;\n}\n"),
]
);
}







public function getPriority(): int
{
return parent::getPriority();
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_ELSE)) {
continue;
}


if ($tokens[$tokens->getNextMeaningfulToken($index)]->equalsAny([':', [T_IF]])) {
continue;
}


$this->fixEmptyElse($tokens, $index);
if ($tokens->isEmptyAt($index)) {
continue;
}


if ($this->isSuperfluousElse($tokens, $index)) {
$this->clearElse($tokens, $index);
}
}
}






private function fixEmptyElse(Tokens $tokens, int $index): void
{
$next = $tokens->getNextMeaningfulToken($index);

if ($tokens[$next]->equals('{')) {
$close = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $next);
if (1 === $close - $next) { 
$this->clearElse($tokens, $index);
} elseif ($tokens->getNextMeaningfulToken($next) === $close) { 
$this->clearElse($tokens, $index);
}

return;
}


$end = $tokens->getNextTokenOfKind($index, [';', [T_CLOSE_TAG]]);
if ($next === $end) {
$this->clearElse($tokens, $index);
}
}




private function clearElse(Tokens $tokens, int $index): void
{
$tokens->clearTokenAndMergeSurroundingWhitespace($index);


$next = $tokens->getNextMeaningfulToken($index);

if (!$tokens[$next]->equals('{')) {
return;
}

$tokens->clearTokenAndMergeSurroundingWhitespace($tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $next));
$tokens->clearTokenAndMergeSurroundingWhitespace($next);
}
}
