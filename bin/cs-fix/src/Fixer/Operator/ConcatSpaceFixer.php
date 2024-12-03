<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Operator;

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
final class ConcatSpaceFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Concatenation should be spaced according to configuration.',
[
new CodeSample(
"<?php\n\$foo = 'bar' . 3 . 'baz'.'qux';\n"
),
new CodeSample(
"<?php\n\$foo = 'bar' . 3 . 'baz'.'qux';\n",
['spacing' => 'none']
),
new CodeSample(
"<?php\n\$foo = 'bar' . 3 . 'baz'.'qux';\n",
['spacing' => 'one']
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
return $tokens->isTokenKindFound('.');
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = $tokens->count() - 1; $index >= 0; --$index) {
if ($tokens[$index]->equals('.')) {
if ('one' === $this->configuration['spacing']) {
$this->fixConcatenationToSingleSpace($tokens, $index);
} else {
$this->fixConcatenationToNoSpace($tokens, $index);
}
}
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('spacing', 'Spacing to apply around concatenation operator.'))
->setAllowedValues(['one', 'none'])
->setDefault('none')
->getOption(),
]);
}




private function fixConcatenationToNoSpace(Tokens $tokens, int $index): void
{
$prevNonWhitespaceToken = $tokens[$tokens->getPrevNonWhitespace($index)];

if (!$prevNonWhitespaceToken->isGivenKind([T_LNUMBER, T_COMMENT, T_DOC_COMMENT]) || str_starts_with($prevNonWhitespaceToken->getContent(), '/*')) {
$tokens->removeLeadingWhitespace($index, " \t");
}

if (!$tokens[$tokens->getNextNonWhitespace($index)]->isGivenKind([T_LNUMBER, T_COMMENT, T_DOC_COMMENT])) {
$tokens->removeTrailingWhitespace($index, " \t");
}
}




private function fixConcatenationToSingleSpace(Tokens $tokens, int $index): void
{
$this->fixWhiteSpaceAroundConcatToken($tokens, $index, 1);
$this->fixWhiteSpaceAroundConcatToken($tokens, $index, -1);
}





private function fixWhiteSpaceAroundConcatToken(Tokens $tokens, int $index, int $offset): void
{
if (-1 !== $offset && 1 !== $offset) {
throw new \InvalidArgumentException(\sprintf(
'Expected `-1|1` for "$offset", got "%s"',
$offset
));
}

$offsetIndex = $index + $offset;

if (!$tokens[$offsetIndex]->isWhitespace()) {
$tokens->insertAt($index + (1 === $offset ? 1 : 0), new Token([T_WHITESPACE, ' ']));

return;
}

if (str_contains($tokens[$offsetIndex]->getContent(), "\n")) {
return;
}

if ($tokens[$index + $offset * 2]->isComment()) {
return;
}

$tokens[$offsetIndex] = new Token([T_WHITESPACE, ' ']);
}
}
