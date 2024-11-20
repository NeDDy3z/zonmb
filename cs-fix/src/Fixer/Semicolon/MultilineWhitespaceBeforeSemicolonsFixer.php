<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Semicolon;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
@implements
@phpstan-type
@phpstan-type








*/
final class MultilineWhitespaceBeforeSemicolonsFixer extends AbstractFixer implements ConfigurableFixerInterface, WhitespacesAwareFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




public const STRATEGY_NO_MULTI_LINE = 'no_multi_line';




public const STRATEGY_NEW_LINE_FOR_CHAINED_CALLS = 'new_line_for_chained_calls';

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Forbid multi-line whitespace before the closing semicolon or move the semicolon to the new line for chained calls.',
[
new CodeSample(
'<?php
function foo() {
    return 1 + 2
        ;
}
'
),
new CodeSample(
'<?php
$object->method1()
    ->method2()
    ->method(3);
',
['strategy' => self::STRATEGY_NEW_LINE_FOR_CHAINED_CALLS]
),
]
);
}







public function getPriority(): int
{
return 0;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(';');
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder(
'strategy',
'Forbid multi-line whitespace or move the semicolon to the new line for chained calls.'
))
->setAllowedValues([self::STRATEGY_NO_MULTI_LINE, self::STRATEGY_NEW_LINE_FOR_CHAINED_CALLS])
->setDefault(self::STRATEGY_NO_MULTI_LINE)
->getOption(),
]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$lineEnding = $this->whitespacesConfig->getLineEnding();

for ($index = 0, $count = \count($tokens); $index < $count; ++$index) {
if (!$tokens[$index]->equals(';')) {
continue;
}

$previousIndex = $index - 1;
$previous = $tokens[$previousIndex];

$indent = $this->findWhitespaceBeforeFirstCall($index, $tokens);
if (self::STRATEGY_NEW_LINE_FOR_CHAINED_CALLS === $this->configuration['strategy'] && null !== $indent) {
if ($previous->isWhitespace() && $previous->getContent() === $lineEnding.$indent) {
continue;
}


if ($previous->isWhitespace()) {
$tokens->clearAt($previousIndex);
}
$tokens->clearAt($index);


$index = $this->getNewLineIndex($index, $tokens);


$newline = new Token([T_WHITESPACE, $lineEnding.$indent]);


$tokens->insertAt($index++, [$newline, new Token(';')]);
} else {
if (!$previous->isWhitespace() || !str_contains($previous->getContent(), "\n")) {
continue;
}

$content = $previous->getContent();
if (str_starts_with($content, $lineEnding) && $tokens[$index - 2]->isComment()) {



$tokens->clearAt($previousIndex);
$tokens->clearAt($index);


$significantTokenIndex = $this->getPreviousSignificantTokenIndex($index, $tokens);


$tokens->insertAt($significantTokenIndex + 1, [new Token(';')]);
} else {

$tokens->clearAt($previousIndex);
}
}
}
}




private function getNewLineIndex(int $index, Tokens $tokens): int
{
$lineEnding = $this->whitespacesConfig->getLineEnding();

for ($index, $count = \count($tokens); $index < $count; ++$index) {
if (false !== strstr($tokens[$index]->getContent(), $lineEnding)) {
return $index;
}
}

return $index;
}




private function getPreviousSignificantTokenIndex(int $index, Tokens $tokens): int
{
$stopTokens = [
T_LNUMBER,
T_DNUMBER,
T_STRING,
T_VARIABLE,
T_CONSTANT_ENCAPSED_STRING,
];
for ($index; $index > 0; --$index) {
if ($tokens[$index]->isGivenKind($stopTokens) || $tokens[$index]->equals(')')) {
return $index;
}
}

return $index;
}










private function findWhitespaceBeforeFirstCall(int $index, Tokens $tokens): ?string
{
$isMultilineCall = false;
$prevIndex = $tokens->getPrevMeaningfulToken($index);

while (!$tokens[$prevIndex]->equalsAny([';', ':', '{', '}', [T_OPEN_TAG], [T_OPEN_TAG_WITH_ECHO], [T_ELSE]])) {
$index = $prevIndex;
$prevIndex = $tokens->getPrevMeaningfulToken($index);

$blockType = Tokens::detectBlockType($tokens[$index]);
if (null !== $blockType && !$blockType['isStart']) {
$prevIndex = $tokens->findBlockStart($blockType['type'], $index);

continue;
}

if ($tokens[$index]->isObjectOperator() || $tokens[$index]->isGivenKind(T_DOUBLE_COLON)) {
$prevIndex = $tokens->getPrevMeaningfulToken($index);
$isMultilineCall |= $tokens->isPartialCodeMultiline($prevIndex, $index);
}
}

return $isMultilineCall ? WhitespacesAnalyzer::detectIndent($tokens, $index) : null;
}
}
