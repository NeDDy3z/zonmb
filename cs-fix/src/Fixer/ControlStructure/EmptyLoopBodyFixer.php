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
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
@implements
@phpstan-type
@phpstan-type





*/
final class EmptyLoopBodyFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

private const STYLE_BRACES = 'braces';

private const STYLE_SEMICOLON = 'semicolon';

private const TOKEN_LOOP_KINDS = [T_FOR, T_FOREACH, T_WHILE];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Empty loop-body must be in configured style.',
[
new CodeSample("<?php while(foo()){}\n"),
new CodeSample(
"<?php while(foo());\n",
[
'style' => 'braces',
]
),
]
);
}







public function getPriority(): int
{
return 39;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound(self::TOKEN_LOOP_KINDS);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
if (self::STYLE_BRACES === $this->configuration['style']) {
$analyzer = new TokensAnalyzer($tokens);
$fixLoop = static function (int $index, int $endIndex) use ($tokens, $analyzer): void {
if ($tokens[$index]->isGivenKind(T_WHILE) && $analyzer->isWhilePartOfDoWhile($index)) {
return;
}

$semiColonIndex = $tokens->getNextMeaningfulToken($endIndex);

if (!$tokens[$semiColonIndex]->equals(';')) {
return;
}

$tokens[$semiColonIndex] = new Token('{');
$tokens->insertAt($semiColonIndex + 1, new Token('}'));
};
} else {
$fixLoop = static function (int $index, int $endIndex) use ($tokens): void {
$braceOpenIndex = $tokens->getNextMeaningfulToken($endIndex);

if (!$tokens[$braceOpenIndex]->equals('{')) {
return;
}

$braceCloseIndex = $tokens->getNextNonWhitespace($braceOpenIndex);

if (!$tokens[$braceCloseIndex]->equals('}')) {
return;
}

$tokens[$braceOpenIndex] = new Token(';');
$tokens->clearTokenAndMergeSurroundingWhitespace($braceCloseIndex);
};
}

for ($index = $tokens->count() - 1; $index > 0; --$index) {
if ($tokens[$index]->isGivenKind(self::TOKEN_LOOP_KINDS)) {
$endIndex = $tokens->getNextTokenOfKind($index, ['(']); 
$endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $endIndex); 
$fixLoop($index, $endIndex); 
}
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('style', 'Style of empty loop-bodies.'))
->setAllowedTypes(['string'])
->setAllowedValues([self::STYLE_BRACES, self::STYLE_SEMICOLON])
->setDefault(self::STYLE_SEMICOLON)
->getOption(),
]);
}
}
