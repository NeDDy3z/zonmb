<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;







final class NoSpacesAfterFunctionNameFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'When making a method or function call, there MUST NOT be a space between the method or function name and the opening parenthesis.',
[new CodeSample("<?php\nstrlen ('Hello World!');\nfoo (test (3));\nexit  (1);\n\$func ();\n")]
);
}







public function getPriority(): int
{
return 3;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound([T_STRING, ...$this->getFunctionyTokenKinds()]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$functionyTokens = $this->getFunctionyTokenKinds();
$languageConstructionTokens = $this->getLanguageConstructionTokenKinds();
$braceTypes = $this->getBraceAfterVariableKinds();

foreach ($tokens as $index => $token) {

if (!$token->equals('(')) {
continue;
}


$lastTokenIndex = $tokens->getPrevNonWhitespace($index);


$endParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
$nextNonWhiteSpace = $tokens->getNextMeaningfulToken($endParenthesisIndex);
if (
null !== $nextNonWhiteSpace
&& !$tokens[$nextNonWhiteSpace]->equals(';')
&& $tokens[$lastTokenIndex]->isGivenKind($languageConstructionTokens)
) {
continue;
}


if ($tokens[$lastTokenIndex]->isGivenKind($functionyTokens)) {
$this->fixFunctionCall($tokens, $index);
} elseif ($tokens[$lastTokenIndex]->isGivenKind(T_STRING)) { 
$possibleDefinitionIndex = $tokens->getPrevMeaningfulToken($lastTokenIndex);
if (!$tokens[$possibleDefinitionIndex]->isGivenKind(T_FUNCTION)) {
$this->fixFunctionCall($tokens, $index);
}
} elseif ($tokens[$lastTokenIndex]->equalsAny($braceTypes)) {
$block = Tokens::detectBlockType($tokens[$lastTokenIndex]);
if (
Tokens::BLOCK_TYPE_ARRAY_INDEX_CURLY_BRACE === $block['type']
|| Tokens::BLOCK_TYPE_DYNAMIC_VAR_BRACE === $block['type']
|| Tokens::BLOCK_TYPE_INDEX_SQUARE_BRACE === $block['type']
|| Tokens::BLOCK_TYPE_PARENTHESIS_BRACE === $block['type']
) {
$this->fixFunctionCall($tokens, $index);
}
}
}
}







private function fixFunctionCall(Tokens $tokens, int $index): void
{

if ($tokens[$index - 1]->isWhitespace()) {
$tokens->clearAt($index - 1);
}
}




private function getBraceAfterVariableKinds(): array
{
return [
')',
']',
[CT::T_DYNAMIC_VAR_BRACE_CLOSE],
[CT::T_ARRAY_INDEX_CURLY_BRACE_CLOSE],
];
}






private function getFunctionyTokenKinds(): array
{
static $tokens = [
T_ARRAY,
T_ECHO,
T_EMPTY,
T_EVAL,
T_EXIT,
T_INCLUDE,
T_INCLUDE_ONCE,
T_ISSET,
T_LIST,
T_PRINT,
T_REQUIRE,
T_REQUIRE_ONCE,
T_UNSET,
T_VARIABLE,
];

return $tokens;
}






private function getLanguageConstructionTokenKinds(): array
{
static $languageConstructionTokens = [
T_ECHO,
T_PRINT,
T_INCLUDE,
T_INCLUDE_ONCE,
T_REQUIRE,
T_REQUIRE_ONCE,
];

return $languageConstructionTokens;
}
}
