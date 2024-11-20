<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\NamespaceNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symfony\Component\OptionsResolver\Options;

/**
@implements
@phpstan-type
@phpstan-type










*/
final class BlankLinesBeforeNamespaceFixer extends AbstractFixer implements WhitespacesAwareFixerInterface, ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Controls blank lines before a namespace declaration.',
[
new CodeSample("<?php  namespace A {}\n"),
new CodeSample("<?php  namespace A {}\n", ['min_line_breaks' => 1]),
new CodeSample("<?php\n\ndeclare(strict_types=1);\n\n\n\nnamespace A{}\n", ['max_line_breaks' => 2]),
new CodeSample("<?php\n\n/** Some comment */\nnamespace A{}\n", ['min_line_breaks' => 2]),
new CodeSample("<?php\n\nnamespace A{}\n", ['min_line_breaks' => 0, 'max_line_breaks' => 0]),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_NAMESPACE);
}






public function getPriority(): int
{
return -31;
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('min_line_breaks', 'Minimum line breaks that should exist before namespace declaration.'))
->setAllowedTypes(['int'])
->setDefault(2)
->setNormalizer(static function (Options $options, int $value): int {
if ($value < 0) {
throw new InvalidFixerConfigurationException(
(new self())->getName(),
'Option `min_line_breaks` cannot be lower than 0.'
);
}

return $value;
})
->getOption(),
(new FixerOptionBuilder('max_line_breaks', 'Maximum line breaks that should exist before namespace declaration.'))
->setAllowedTypes(['int'])
->setDefault(2)
->setNormalizer(static function (Options $options, int $value): int {
if ($value < 0) {
throw new InvalidFixerConfigurationException(
(new self())->getName(),
'Option `max_line_breaks` cannot be lower than 0.'
);
}

if ($value < $options['min_line_breaks']) {
throw new InvalidFixerConfigurationException(
(new self())->getName(),
'Option `max_line_breaks` cannot have lower value than `min_line_breaks`.'
);
}

return $value;
})
->getOption(),
]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = $tokens->count() - 1; $index >= 0; --$index) {
$token = $tokens[$index];

if ($token->isGivenKind(T_NAMESPACE)) {
$this->fixLinesBeforeNamespace(
$tokens,
$index,
$this->configuration['min_line_breaks'],
$this->configuration['max_line_breaks']
);
}
}
}







protected function fixLinesBeforeNamespace(Tokens $tokens, int $index, int $expectedMin, int $expectedMax): void
{


$openingTokenIndex = null;
$precedingNewlines = 0;
$newlineInOpening = false;
$openingToken = null;

for ($i = 1; $i <= 2; ++$i) {
if (isset($tokens[$index - $i])) {
$token = $tokens[$index - $i];

if ($token->isGivenKind(T_OPEN_TAG)) {
$openingToken = $token;
$openingTokenIndex = $index - $i;
$newlineInOpening = str_contains($token->getContent(), "\n");

if ($newlineInOpening) {
++$precedingNewlines;
}

break;
}

if (false === $token->isGivenKind(T_WHITESPACE)) {
break;
}

$precedingNewlines += substr_count($token->getContent(), "\n");
}
}

if ($precedingNewlines >= $expectedMin && $precedingNewlines <= $expectedMax) {
return;
}

$previousIndex = $index - 1;
$previous = $tokens[$previousIndex];

if (0 === $expectedMax) {

if ($previous->isWhitespace()) {
$tokens->clearAt($previousIndex);
}


if ($newlineInOpening) {
$tokens[$openingTokenIndex] = new Token([T_OPEN_TAG, rtrim($openingToken->getContent()).' ']);
}

return;
}

$lineEnding = $this->whitespacesConfig->getLineEnding();





$newlinesForWhitespaceToken = $precedingNewlines >= $expectedMax
? $expectedMax
: max($precedingNewlines, $expectedMin);

if (null !== $openingToken) {

$content = rtrim($openingToken->getContent());
$newContent = $content.$lineEnding;
$tokens[$openingTokenIndex] = new Token([T_OPEN_TAG, $newContent]);
--$newlinesForWhitespaceToken;
}

if (0 === $newlinesForWhitespaceToken) {

if ($previous->isWhitespace()) {

$tokens->clearAt($previousIndex);
}

return;
}

if ($previous->isWhitespace()) {

$tokens[$previousIndex] = new Token(
[
T_WHITESPACE,
str_repeat($lineEnding, $newlinesForWhitespaceToken).substr(
$previous->getContent(),
strrpos($previous->getContent(), "\n") + 1
),
]
);
} else {

$tokens->insertAt($index, new Token([T_WHITESPACE, str_repeat($lineEnding, $newlinesForWhitespaceToken)]));
}
}
}
