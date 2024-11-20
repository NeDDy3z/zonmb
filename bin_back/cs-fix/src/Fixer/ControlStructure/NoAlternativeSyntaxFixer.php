<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
@implements
@phpstan-type
@phpstan-type







*/
final class NoAlternativeSyntaxFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Replace control structure alternative syntax to use braces.',
[
new CodeSample(
"<?php\nif(true):echo 't';else:echo 'f';endif;\n"
),
new CodeSample(
"<?php if (\$condition): ?>\nLorem ipsum.\n<?php endif; ?>\n",
['fix_non_monolithic_code' => true]
),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->hasAlternativeSyntax() && (true === $this->configuration['fix_non_monolithic_code'] || $tokens->isMonolithicPhp());
}






public function getPriority(): int
{
return 42;
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('fix_non_monolithic_code', 'Whether to also fix code with inline HTML.'))
->setAllowedTypes(['bool'])
->setDefault(true) 
->getOption(),
]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = \count($tokens) - 1; 0 <= $index; --$index) {
$token = $tokens[$index];
$this->fixElseif($index, $token, $tokens);
$this->fixElse($index, $token, $tokens);
$this->fixOpenCloseControls($index, $token, $tokens);
}
}

private function findParenthesisEnd(Tokens $tokens, int $structureTokenIndex): int
{
$nextIndex = $tokens->getNextMeaningfulToken($structureTokenIndex);
$nextToken = $tokens[$nextIndex];

return $nextToken->equals('(')
? $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $nextIndex)
: $structureTokenIndex; 
}









private function fixOpenCloseControls(int $index, Token $token, Tokens $tokens): void
{
if ($token->isGivenKind([T_IF, T_FOREACH, T_WHILE, T_FOR, T_SWITCH, T_DECLARE])) {
$openIndex = $tokens->getNextTokenOfKind($index, ['(']);
$closeIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openIndex);
$afterParenthesisIndex = $tokens->getNextMeaningfulToken($closeIndex);
$afterParenthesis = $tokens[$afterParenthesisIndex];

if (!$afterParenthesis->equals(':')) {
return;
}

$items = [];

if (!$tokens[$afterParenthesisIndex - 1]->isWhitespace()) {
$items[] = new Token([T_WHITESPACE, ' ']);
}

$items[] = new Token('{');

if (!$tokens[$afterParenthesisIndex + 1]->isWhitespace()) {
$items[] = new Token([T_WHITESPACE, ' ']);
}

$tokens->clearAt($afterParenthesisIndex);
$tokens->insertAt($afterParenthesisIndex, $items);
}

if (!$token->isGivenKind([T_ENDIF, T_ENDFOREACH, T_ENDWHILE, T_ENDFOR, T_ENDSWITCH, T_ENDDECLARE])) {
return;
}

$nextTokenIndex = $tokens->getNextMeaningfulToken($index);
$nextToken = $tokens[$nextTokenIndex];
$tokens[$index] = new Token('}');

if ($nextToken->equals(';')) {
$tokens->clearAt($nextTokenIndex);
}
}








private function fixElse(int $index, Token $token, Tokens $tokens): void
{
if (!$token->isGivenKind(T_ELSE)) {
return;
}

$tokenAfterElseIndex = $tokens->getNextMeaningfulToken($index);
$tokenAfterElse = $tokens[$tokenAfterElseIndex];

if (!$tokenAfterElse->equals(':')) {
return;
}

$this->addBraces($tokens, new Token([T_ELSE, 'else']), $index, $tokenAfterElseIndex);
}








private function fixElseif(int $index, Token $token, Tokens $tokens): void
{
if (!$token->isGivenKind(T_ELSEIF)) {
return;
}

$parenthesisEndIndex = $this->findParenthesisEnd($tokens, $index);
$tokenAfterParenthesisIndex = $tokens->getNextMeaningfulToken($parenthesisEndIndex);
$tokenAfterParenthesis = $tokens[$tokenAfterParenthesisIndex];

if (!$tokenAfterParenthesis->equals(':')) {
return;
}

$this->addBraces($tokens, new Token([T_ELSEIF, 'elseif']), $index, $tokenAfterParenthesisIndex);
}









private function addBraces(Tokens $tokens, Token $token, int $index, int $colonIndex): void
{
$items = [
new Token('}'),
new Token([T_WHITESPACE, ' ']),
$token,
];

if (!$tokens[$index + 1]->isWhitespace()) {
$items[] = new Token([T_WHITESPACE, ' ']);
}

$tokens->clearAt($index);
$tokens->insertAt(
$index,
$items
);


$colonIndex += \count($items);

$items = [new Token('{')];

if (!$tokens[$colonIndex + 1]->isWhitespace()) {
$items[] = new Token([T_WHITESPACE, ' ']);
}

$tokens->clearAt($colonIndex);
$tokens->insertAt(
$colonIndex,
$items
);
}
}
