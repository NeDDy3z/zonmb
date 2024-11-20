<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ArrayNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
@implements
@phpstan-type
@phpstan-type









*/
final class ArraySyntaxFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




private $candidateTokenKind;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'PHP arrays should be declared using the configured syntax.',
[
new CodeSample(
"<?php\narray(1,2);\n"
),
new CodeSample(
"<?php\n[1,2];\n",
['syntax' => 'long']
),
]
);
}






public function getPriority(): int
{
return 37;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound($this->candidateTokenKind);
}

protected function configurePostNormalisation(): void
{
$this->resolveCandidateTokenKind();
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
if ($tokens[$index]->isGivenKind($this->candidateTokenKind)) {
if ('short' === $this->configuration['syntax']) {
$this->fixToShortArraySyntax($tokens, $index);
} else {
$this->fixToLongArraySyntax($tokens, $index);
}
}
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('syntax', 'Whether to use the `long` or `short` array syntax.'))
->setAllowedValues(['long', 'short'])
->setDefault('short')
->getOption(),
]);
}

private function fixToLongArraySyntax(Tokens $tokens, int $index): void
{
$closeIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $index);

$tokens[$index] = new Token('(');
$tokens[$closeIndex] = new Token(')');

$tokens->insertAt($index, new Token([T_ARRAY, 'array']));
}

private function fixToShortArraySyntax(Tokens $tokens, int $index): void
{
$openIndex = $tokens->getNextTokenOfKind($index, ['(']);
$closeIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openIndex);

$tokens[$openIndex] = new Token([CT::T_ARRAY_SQUARE_BRACE_OPEN, '[']);
$tokens[$closeIndex] = new Token([CT::T_ARRAY_SQUARE_BRACE_CLOSE, ']']);

$tokens->clearTokenAndMergeSurroundingWhitespace($index);
}

private function resolveCandidateTokenKind(): void
{
$this->candidateTokenKind = 'long' === $this->configuration['syntax'] ? CT::T_ARRAY_SQUARE_BRACE_OPEN : T_ARRAY;
}
}
