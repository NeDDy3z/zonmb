<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\LanguageConstruct;

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
final class DeclareEqualNormalizeFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Equal sign in declare statement should be surrounded by spaces or not following configuration.',
[
new CodeSample("<?php\ndeclare(ticks =  1);\n"),
new CodeSample("<?php\ndeclare(ticks=1);\n", ['space' => 'single']),
]
);
}






public function getPriority(): int
{
return 0;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DECLARE);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = 0, $count = $tokens->count(); $index < $count - 6; ++$index) {
if (!$tokens[$index]->isGivenKind(T_DECLARE)) {
continue;
}

$openParenthesisIndex = $tokens->getNextMeaningfulToken($index);
$closeParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesisIndex);

for ($i = $closeParenthesisIndex; $i > $openParenthesisIndex; --$i) {
if ($tokens[$i]->equals('=')) {
if ('none' === $this->configuration['space']) {
$this->removeWhitespaceAroundToken($tokens, $i);
} else {
$this->ensureWhitespaceAroundToken($tokens, $i);
}
}
}
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('space', 'Spacing to apply around the equal sign.'))
->setAllowedValues(['single', 'none'])
->setDefault('none')
->getOption(),
]);
}




private function ensureWhitespaceAroundToken(Tokens $tokens, int $index): void
{
if ($tokens[$index + 1]->isWhitespace()) {
if (' ' !== $tokens[$index + 1]->getContent()) {
$tokens[$index + 1] = new Token([T_WHITESPACE, ' ']);
}
} else {
$tokens->insertAt($index + 1, new Token([T_WHITESPACE, ' ']));
}

if ($tokens[$index - 1]->isWhitespace()) {
if (' ' !== $tokens[$index - 1]->getContent() && !$tokens[$tokens->getPrevNonWhitespace($index - 1)]->isComment()) {
$tokens[$index - 1] = new Token([T_WHITESPACE, ' ']);
}
} else {
$tokens->insertAt($index, new Token([T_WHITESPACE, ' ']));
}
}




private function removeWhitespaceAroundToken(Tokens $tokens, int $index): void
{
if (!$tokens[$tokens->getPrevNonWhitespace($index)]->isComment()) {
$tokens->removeLeadingWhitespace($index);
}

$tokens->removeTrailingWhitespace($index);
}
}
