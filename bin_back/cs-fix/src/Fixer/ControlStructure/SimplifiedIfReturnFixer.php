<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class SimplifiedIfReturnFixer extends AbstractFixer
{



private array $sequences = [
[
'isNegative' => false,
'sequence' => [
'{', [T_RETURN], [T_STRING, 'true'], ';', '}',
[T_RETURN], [T_STRING, 'false'], ';',
],
],
[
'isNegative' => true,
'sequence' => [
'{', [T_RETURN], [T_STRING, 'false'], ';', '}',
[T_RETURN], [T_STRING, 'true'], ';',
],
],
[
'isNegative' => false,
'sequence' => [
[T_RETURN], [T_STRING, 'true'], ';',
[T_RETURN], [T_STRING, 'false'], ';',
],
],
[
'isNegative' => true,
'sequence' => [
[T_RETURN], [T_STRING, 'false'], ';',
[T_RETURN], [T_STRING, 'true'], ';',
],
],
];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Simplify `if` control structures that return the boolean result of their condition.',
[new CodeSample("<?php\nif (\$foo) { return true; } return false;\n")]
);
}







public function getPriority(): int
{
return 1;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound([T_IF, T_RETURN, T_STRING]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($ifIndex = $tokens->count() - 1; 0 <= $ifIndex; --$ifIndex) {
if (!$tokens[$ifIndex]->isGivenKind([T_IF, T_ELSEIF])) {
continue;
}

if ($tokens[$tokens->getPrevMeaningfulToken($ifIndex)]->equals(')')) {
continue; 
}

$startParenthesisIndex = $tokens->getNextTokenOfKind($ifIndex, ['(']);
$endParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $startParenthesisIndex);
$firstCandidateIndex = $tokens->getNextMeaningfulToken($endParenthesisIndex);

foreach ($this->sequences as $sequenceSpec) {
$sequenceFound = $tokens->findSequence($sequenceSpec['sequence'], $firstCandidateIndex);

if (null === $sequenceFound) {
continue;
}

$firstSequenceIndex = array_key_first($sequenceFound);

if ($firstSequenceIndex !== $firstCandidateIndex) {
continue;
}

$indicesToClear = array_keys($sequenceFound);
array_pop($indicesToClear); 
rsort($indicesToClear);

foreach ($indicesToClear as $index) {
$tokens->clearTokenAndMergeSurroundingWhitespace($index);
}

$newTokens = [
new Token([T_RETURN, 'return']),
new Token([T_WHITESPACE, ' ']),
];

if ($sequenceSpec['isNegative']) {
$newTokens[] = new Token('!');
} else {
$newTokens[] = new Token([T_BOOL_CAST, '(bool)']);
}

$tokens->overrideRange($ifIndex, $ifIndex, $newTokens);
}
}
}
}
