<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
@implements
@phpstan-type
@phpstan-type











*/
final class TrailingCommaInMultilineFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




public const ELEMENTS_ARRAYS = 'arrays';




public const ELEMENTS_ARGUMENTS = 'arguments';




public const ELEMENTS_PARAMETERS = 'parameters';

private const MATCH_EXPRESSIONS = 'match';

private const ARRAY_DESTRUCTURING = 'array_destructuring';

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Arguments lists, array destructuring lists, arrays that are multi-line, `match`-lines and parameters lists must have a trailing comma.',
[
new CodeSample("<?php\narray(\n    1,\n    2\n);\n"),
new CodeSample(
<<<'SAMPLE'
                        <?php
                            $x = [
                                'foo',
                                <<<EOD
                                    bar
                                    EOD
                            ];

                        SAMPLE
,
['after_heredoc' => true]
),
new CodeSample("<?php\nfoo(\n    1,\n    2\n);\n", ['elements' => [self::ELEMENTS_ARGUMENTS]]),
new VersionSpecificCodeSample("<?php\nfunction foo(\n    \$x,\n    \$y\n)\n{\n}\n", new VersionSpecification(8_00_00), ['elements' => [self::ELEMENTS_PARAMETERS]]),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound([T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN, '(', CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN]);
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('after_heredoc', 'Whether a trailing comma should also be placed after heredoc end.'))
->setAllowedTypes(['bool'])
->setDefault(false) 
->getOption(),
(new FixerOptionBuilder('elements', \sprintf('Where to fix multiline trailing comma (PHP >= 8.0 for `%s` and `%s`).', self::ELEMENTS_PARAMETERS, self::MATCH_EXPRESSIONS))) 
->setAllowedTypes(['string[]'])
->setAllowedValues([
new AllowedValueSubset([
self::ARRAY_DESTRUCTURING,
self::ELEMENTS_ARGUMENTS,
self::ELEMENTS_ARRAYS,
self::ELEMENTS_PARAMETERS,
self::MATCH_EXPRESSIONS,
]),
])
->setDefault([self::ELEMENTS_ARRAYS])
->getOption(),
]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$configuredElements = $this->configuration['elements'];
$fixArrays = \in_array(self::ELEMENTS_ARRAYS, $configuredElements, true);
$fixArguments = \in_array(self::ELEMENTS_ARGUMENTS, $configuredElements, true);
$fixParameters = \PHP_VERSION_ID >= 8_00_00 && \in_array(self::ELEMENTS_PARAMETERS, $configuredElements, true); 
$fixMatch = \PHP_VERSION_ID >= 8_00_00 && \in_array(self::MATCH_EXPRESSIONS, $configuredElements, true); 
$fixDestructuring = \in_array(self::ARRAY_DESTRUCTURING, $configuredElements, true);

for ($index = $tokens->count() - 1; $index >= 0; --$index) {
if ($tokens[$index]->isGivenKind(CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN)) {
if ($fixDestructuring) { 
$this->fixBlock($tokens, $index);
}

continue;
}

if ($tokens[$index]->isGivenKind(CT::T_ARRAY_SQUARE_BRACE_OPEN)) {
if ($fixArrays) { 
$this->fixBlock($tokens, $index);
}

continue;
}

if (!$tokens[$index]->equals('(')) {
continue;
}

$prevIndex = $tokens->getPrevMeaningfulToken($index);

if ($tokens[$prevIndex]->isGivenKind(T_ARRAY)) {
if ($fixArrays) { 
$this->fixBlock($tokens, $index);
}

continue;
}

if ($tokens[$prevIndex]->isGivenKind(T_LIST)) {
if ($fixDestructuring || $fixArguments) { 
$this->fixBlock($tokens, $index);
}

continue;
}

if ($fixMatch && $tokens[$prevIndex]->isGivenKind(T_MATCH)) {
$this->fixMatch($tokens, $index);

continue;
}

$prevPrevIndex = $tokens->getPrevMeaningfulToken($prevIndex);

if ($fixArguments
&& $tokens[$prevIndex]->equalsAny([']', [T_CLASS], [T_STRING], [T_VARIABLE], [T_STATIC], [T_ISSET], [T_UNSET], [T_LIST]])
&& !$tokens[$prevPrevIndex]->isGivenKind(T_FUNCTION)
) {
$this->fixBlock($tokens, $index);

continue;
}

if (
$fixParameters
&& (
$tokens[$prevIndex]->isGivenKind(T_STRING)
&& $tokens[$prevPrevIndex]->isGivenKind(T_FUNCTION)
|| $tokens[$prevIndex]->isGivenKind([T_FN, T_FUNCTION])
)
) {
$this->fixBlock($tokens, $index);
}
}
}

private function fixBlock(Tokens $tokens, int $startIndex): void
{
$tokensAnalyzer = new TokensAnalyzer($tokens);

if (!$tokensAnalyzer->isBlockMultiline($tokens, $startIndex)) {
return;
}

$blockType = Tokens::detectBlockType($tokens[$startIndex]);
$endIndex = $tokens->findBlockEnd($blockType['type'], $startIndex);

$beforeEndIndex = $tokens->getPrevMeaningfulToken($endIndex);
if (!$tokens->isPartialCodeMultiline($beforeEndIndex, $endIndex)) {
return;
}
$beforeEndToken = $tokens[$beforeEndIndex];


if (
$startIndex !== $beforeEndIndex && !$beforeEndToken->equals(',')
&& (true === $this->configuration['after_heredoc'] || !$beforeEndToken->isGivenKind(T_END_HEREDOC))
) {
$tokens->insertAt($beforeEndIndex + 1, new Token(','));

$endToken = $tokens[$endIndex];

if (!$endToken->isComment() && !$endToken->isWhitespace()) {
$tokens->ensureWhitespaceAtIndex($endIndex, 1, ' ');
}
}
}

private function fixMatch(Tokens $tokens, int $index): void
{
$index = $tokens->getNextTokenOfKind($index, ['{']);
$closeIndex = $index;
$isMultiline = false;
$depth = 1;

do {
++$closeIndex;

if ($tokens[$closeIndex]->equals('{')) {
++$depth;
} elseif ($tokens[$closeIndex]->equals('}')) {
--$depth;
} elseif (!$isMultiline && str_contains($tokens[$closeIndex]->getContent(), "\n")) {
$isMultiline = true;
}
} while ($depth > 0);

if (!$isMultiline) {
return;
}

$previousIndex = $tokens->getPrevMeaningfulToken($closeIndex);
if (!$tokens->isPartialCodeMultiline($previousIndex, $closeIndex)) {
return;
}

if (!$tokens[$previousIndex]->equals(',')) {
$tokens->insertAt($previousIndex + 1, new Token(','));
}
}
}
