<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

final class StaticLambdaFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Lambdas not (indirectly) referencing `$this` must be declared `static`.',
[new CodeSample("<?php\n\$a = function () use (\$b)\n{   echo \$b;\n};\n")],
null,
'Risky when using `->bindTo` on lambdas without referencing to `$this`.'
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound([T_FUNCTION, T_FN]);
}

public function isRisky(): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$analyzer = new TokensAnalyzer($tokens);
$expectedFunctionKinds = [T_FUNCTION, T_FN];

for ($index = $tokens->count() - 4; $index > 0; --$index) {
if (!$tokens[$index]->isGivenKind($expectedFunctionKinds) || !$analyzer->isLambda($index)) {
continue;
}

$prev = $tokens->getPrevMeaningfulToken($index);

if ($tokens[$prev]->isGivenKind(T_STATIC)) {
continue; 
}

$argumentsStartIndex = $tokens->getNextTokenOfKind($index, ['(']);
$argumentsEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $argumentsStartIndex);



if ($tokens[$index]->isGivenKind(T_FUNCTION)) {
$lambdaOpenIndex = $tokens->getNextTokenOfKind($argumentsEndIndex, ['{']);
$lambdaEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $lambdaOpenIndex);
} else { 
$lambdaOpenIndex = $tokens->getNextTokenOfKind($argumentsEndIndex, [[T_DOUBLE_ARROW]]);
$lambdaEndIndex = $analyzer->getLastTokenIndexOfArrowFunction($index);
}

if ($this->hasPossibleReferenceToThis($tokens, $lambdaOpenIndex, $lambdaEndIndex)) {
continue;
}


$tokens->insertAt(
$index,
[
new Token([T_STATIC, 'static']),
new Token([T_WHITESPACE, ' ']),
]
);

$index -= 4; 
}
}




private function hasPossibleReferenceToThis(Tokens $tokens, int $startIndex, int $endIndex): bool
{
for ($i = $startIndex; $i <= $endIndex; ++$i) {
if ($tokens[$i]->isGivenKind(T_VARIABLE) && '$this' === strtolower($tokens[$i]->getContent())) {
return true; 
}

if ($tokens[$i]->isGivenKind([
T_INCLUDE, 
T_INCLUDE_ONCE, 
T_REQUIRE, 
T_REQUIRE_ONCE, 
CT::T_DYNAMIC_VAR_BRACE_OPEN, 
T_EVAL, 
])) {
return true;
}

if ($tokens[$i]->equals('$')) {
$nextIndex = $tokens->getNextMeaningfulToken($i);

if ($tokens[$nextIndex]->isGivenKind(T_VARIABLE)) {
return true; 
}
}

if ($tokens[$i]->equals([T_STRING, 'parent'], false)) {
return true; 
}
}

return false;
}
}
