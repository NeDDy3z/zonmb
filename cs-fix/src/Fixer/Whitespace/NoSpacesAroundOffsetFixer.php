<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Whitespace;

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
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
@implements
@phpstan-type
@phpstan-type







*/
final class NoSpacesAroundOffsetFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'There MUST NOT be spaces around offset braces.',
[
new CodeSample("<?php\n\$sample = \$b [ 'a' ] [ 'b' ];\n"),
new CodeSample("<?php\n\$sample = \$b [ 'a' ] [ 'b' ];\n", ['positions' => ['inside']]),
new CodeSample("<?php\n\$sample = \$b [ 'a' ] [ 'b' ];\n", ['positions' => ['outside']]),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound(['[', CT::T_ARRAY_INDEX_CURLY_BRACE_OPEN]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->equalsAny(['[', [CT::T_ARRAY_INDEX_CURLY_BRACE_OPEN]])) {
continue;
}

if (\in_array('inside', $this->configuration['positions'], true)) {
if ($token->equals('[')) {
$endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_INDEX_SQUARE_BRACE, $index);
} else {
$endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_INDEX_CURLY_BRACE, $index);
}


if ($tokens[$index + 1]->isWhitespace(" \t")) {
$tokens->clearAt($index + 1);
}


if ($tokens[$endIndex - 1]->isWhitespace(" \t")) {
$tokens->clearAt($endIndex - 1);
}
}

if (\in_array('outside', $this->configuration['positions'], true)) {
$prevNonWhitespaceIndex = $tokens->getPrevNonWhitespace($index);
if ($tokens[$prevNonWhitespaceIndex]->isComment()) {
continue;
}

$tokens->removeLeadingWhitespace($index);
}
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
$values = ['inside', 'outside'];

return new FixerConfigurationResolver([
(new FixerOptionBuilder('positions', 'Whether spacing should be fixed inside and/or outside the offset braces.'))
->setAllowedTypes(['string[]'])
->setAllowedValues([new AllowedValueSubset($values)])
->setDefault($values)
->getOption(),
]);
}
}
