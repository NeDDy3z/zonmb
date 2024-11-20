<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFopenFlagFixer;
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
final class FopenFlagsFixer extends AbstractFopenFlagFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'The flags in `fopen` calls must omit `t`, and `b` must be omitted or included consistently.',
[
new CodeSample("<?php\n\$a = fopen(\$foo, 'rwt');\n"),
new CodeSample("<?php\n\$a = fopen(\$foo, 'rwt');\n", ['b_mode' => false]),
],
null,
'Risky when the function `fopen` is overridden.'
);
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('b_mode', 'The `b` flag must be used (`true`) or omitted (`false`).'))
->setAllowedTypes(['bool'])
->setDefault(true)
->getOption(),
]);
}

protected function fixFopenFlagToken(Tokens $tokens, int $argumentStartIndex, int $argumentEndIndex): void
{
$argumentFlagIndex = null;

for ($i = $argumentStartIndex; $i <= $argumentEndIndex; ++$i) {
if ($tokens[$i]->isGivenKind([T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
continue;
}

if (null !== $argumentFlagIndex) {
return; 
}

$argumentFlagIndex = $i;
}


if (null === $argumentFlagIndex || !$tokens[$argumentFlagIndex]->isGivenKind(T_CONSTANT_ENCAPSED_STRING)) {
return;
}

$content = $tokens[$argumentFlagIndex]->getContent();
$contentQuote = $content[0]; 

if ('b' === $contentQuote || 'B' === $contentQuote) {
$binPrefix = $contentQuote;
$contentQuote = $content[1]; 
$mode = substr($content, 2, -1);
} else {
$binPrefix = '';
$mode = substr($content, 1, -1);
}

if (false === $this->isValidModeString($mode)) {
return;
}

$mode = str_replace('t', '', $mode);

if (true === $this->configuration['b_mode']) {
if (!str_contains($mode, 'b')) {
$mode .= 'b';
}
} else {
$mode = str_replace('b', '', $mode);
}

$newContent = $binPrefix.$contentQuote.$mode.$contentQuote;

if ($content !== $newContent) {
$tokens[$argumentFlagIndex] = new Token([T_CONSTANT_ENCAPSED_STRING, $newContent]);
}
}
}
