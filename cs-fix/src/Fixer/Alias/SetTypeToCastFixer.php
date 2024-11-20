<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Alias;

use PhpCsFixer\AbstractFunctionReferenceFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class SetTypeToCastFixer extends AbstractFunctionReferenceFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Cast shall be used, not `settype`.',
[
new CodeSample(
'<?php
settype($foo, "integer");
settype($bar, "string");
settype($bar, "null");
'
),
],
null,
'Risky when the `settype` function is overridden or when used as the 2nd or 3rd expression in a `for` loop .'
);
}






public function getPriority(): int
{
return 0;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound([T_CONSTANT_ENCAPSED_STRING, T_STRING, T_VARIABLE]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$map = [
'array' => [T_ARRAY_CAST, '(array)'],
'bool' => [T_BOOL_CAST, '(bool)'],
'boolean' => [T_BOOL_CAST, '(bool)'],
'double' => [T_DOUBLE_CAST, '(float)'],
'float' => [T_DOUBLE_CAST, '(float)'],
'int' => [T_INT_CAST, '(int)'],
'integer' => [T_INT_CAST, '(int)'],
'object' => [T_OBJECT_CAST, '(object)'],
'string' => [T_STRING_CAST, '(string)'],

];

$argumentsAnalyzer = new ArgumentsAnalyzer();

foreach (array_reverse($this->findSettypeCalls($tokens)) as $candidate) {
$functionNameIndex = $candidate[0];

$arguments = $argumentsAnalyzer->getArguments($tokens, $candidate[1], $candidate[2]);
if (2 !== \count($arguments)) {
continue; 
}

$prev = $tokens->getPrevMeaningfulToken($functionNameIndex);

if (!$tokens[$prev]->equalsAny([';', '{', '}', [T_OPEN_TAG]])) {
continue; 
}

reset($arguments);



$firstArgumentStart = key($arguments);
if ($tokens[$firstArgumentStart]->isComment() || $tokens[$firstArgumentStart]->isWhitespace()) {
$firstArgumentStart = $tokens->getNextMeaningfulToken($firstArgumentStart);
}

if (!$tokens[$firstArgumentStart]->isGivenKind(T_VARIABLE)) {
continue; 
}

$commaIndex = $tokens->getNextMeaningfulToken($firstArgumentStart);

if (null === $commaIndex || !$tokens[$commaIndex]->equals(',')) {
continue; 
}



next($arguments);
$secondArgumentStart = key($arguments);
$secondArgumentEnd = $arguments[$secondArgumentStart];

if ($tokens[$secondArgumentStart]->isComment() || $tokens[$secondArgumentStart]->isWhitespace()) {
$secondArgumentStart = $tokens->getNextMeaningfulToken($secondArgumentStart);
}

if (
!$tokens[$secondArgumentStart]->isGivenKind(T_CONSTANT_ENCAPSED_STRING)
|| $tokens->getNextMeaningfulToken($secondArgumentStart) < $secondArgumentEnd
) {
continue; 
}



$type = strtolower(trim($tokens[$secondArgumentStart]->getContent(), '"\''));

if ('null' !== $type && !isset($map[$type])) {
continue; 
}



$argumentToken = $tokens[$firstArgumentStart];

$this->removeSettypeCall(
$tokens,
$functionNameIndex,
$candidate[1],
$firstArgumentStart,
$commaIndex,
$secondArgumentStart,
$candidate[2]
);

if ('null' === $type) {
$this->fixSettypeNullCall($tokens, $functionNameIndex, $argumentToken);
} else {
\assert(isset($map[$type]));
$this->fixSettypeCall($tokens, $functionNameIndex, $argumentToken, new Token($map[$type]));
}
}
}




private function findSettypeCalls(Tokens $tokens): array
{
$candidates = [];

$end = \count($tokens);
for ($i = 1; $i < $end; ++$i) {
$candidate = $this->find('settype', $tokens, $i, $end);
if (null === $candidate) {
break;
}

$i = $candidate[1]; 
$candidates[] = $candidate;
}

return $candidates;
}

private function removeSettypeCall(
Tokens $tokens,
int $functionNameIndex,
int $openParenthesisIndex,
int $firstArgumentStart,
int $commaIndex,
int $secondArgumentStart,
int $closeParenthesisIndex
): void {
$tokens->clearTokenAndMergeSurroundingWhitespace($closeParenthesisIndex);
$prevIndex = $tokens->getPrevMeaningfulToken($closeParenthesisIndex);
if ($tokens[$prevIndex]->equals(',')) {
$tokens->clearTokenAndMergeSurroundingWhitespace($prevIndex);
}
$tokens->clearTokenAndMergeSurroundingWhitespace($secondArgumentStart);
$tokens->clearTokenAndMergeSurroundingWhitespace($commaIndex);
$tokens->clearTokenAndMergeSurroundingWhitespace($firstArgumentStart);
$tokens->clearTokenAndMergeSurroundingWhitespace($openParenthesisIndex);
$tokens->clearAt($functionNameIndex); 
$tokens->clearEmptyTokens();
}

private function fixSettypeCall(
Tokens $tokens,
int $functionNameIndex,
Token $argumentToken,
Token $castToken
): void {
$tokens->insertAt(
$functionNameIndex,
[
clone $argumentToken,
new Token([T_WHITESPACE, ' ']),
new Token('='),
new Token([T_WHITESPACE, ' ']),
$castToken,
new Token([T_WHITESPACE, ' ']),
clone $argumentToken,
]
);

$tokens->removeTrailingWhitespace($functionNameIndex + 6); 
}

private function fixSettypeNullCall(
Tokens $tokens,
int $functionNameIndex,
Token $argumentToken
): void {
$tokens->insertAt(
$functionNameIndex,
[
clone $argumentToken,
new Token([T_WHITESPACE, ' ']),
new Token('='),
new Token([T_WHITESPACE, ' ']),
new Token([T_STRING, 'null']),
]
);

$tokens->removeTrailingWhitespace($functionNameIndex + 4); 
}
}
