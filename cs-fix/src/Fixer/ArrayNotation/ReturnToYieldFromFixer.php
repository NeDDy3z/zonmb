<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ArrayNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class ReturnToYieldFromFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'If the function explicitly returns an array, and has the return type `iterable`, then `yield from` must be used instead of `return`.',
[new CodeSample('<?php function giveMeData(): iterable {
    return [1, 2, 3];
}
')],
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound([T_FUNCTION, T_RETURN]) && $tokens->isAnyTokenKindsFound([T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN]);
}







public function getPriority(): int
{
return 1;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens->findGivenKind(T_RETURN) as $index => $token) {
if (!$this->shouldBeFixed($tokens, $index)) {
continue;
}

$tokens[$index] = new Token([T_YIELD_FROM, 'yield from']);
}
}

private function shouldBeFixed(Tokens $tokens, int $returnIndex): bool
{
$arrayStartIndex = $tokens->getNextMeaningfulToken($returnIndex);
if (!$tokens[$arrayStartIndex]->isGivenKind([T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN])) {
return false;
}

if ($tokens[$arrayStartIndex]->isGivenKind(CT::T_ARRAY_SQUARE_BRACE_OPEN)) {
$arrayEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $arrayStartIndex);
} else {
$arrayOpenParenthesisIndex = $tokens->getNextTokenOfKind($arrayStartIndex, ['(']);
$arrayEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $arrayOpenParenthesisIndex);
}

$functionEndIndex = $arrayEndIndex;
do {
$functionEndIndex = $tokens->getNextMeaningfulToken($functionEndIndex);
} while (null !== $functionEndIndex && $tokens[$functionEndIndex]->equals(';'));
if (null === $functionEndIndex || !$tokens[$functionEndIndex]->equals('}')) {
return false;
}

$functionStartIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_CURLY_BRACE, $functionEndIndex);

$returnTypeIndex = $tokens->getPrevMeaningfulToken($functionStartIndex);
if (!$tokens[$returnTypeIndex]->isGivenKind(T_STRING)) {
return false;
}

if ('iterable' !== strtolower($tokens[$returnTypeIndex]->getContent())) {
return false;
}

$beforeReturnTypeIndex = $tokens->getPrevMeaningfulToken($returnTypeIndex);

return $tokens[$beforeReturnTypeIndex]->isGivenKind(CT::T_TYPE_COLON);
}
}
