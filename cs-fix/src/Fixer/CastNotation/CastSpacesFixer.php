<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\CastNotation;

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
final class CastSpacesFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

private const INSIDE_CAST_SPACE_REPLACE_MAP = [
' ' => '',
"\t" => '',
"\n" => '',
"\r" => '',
"\0" => '',
"\x0B" => '',
];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'A single space or none should be between cast and variable.',
[
new CodeSample(
"<?php\n\$bar = ( string )  \$a;\n\$foo = (int)\$b;\n"
),
new CodeSample(
"<?php\n\$bar = ( string )  \$a;\n\$foo = (int)\$b;\n",
['space' => 'single']
),
new CodeSample(
"<?php\n\$bar = ( string )  \$a;\n\$foo = (int) \$b;\n",
['space' => 'none']
),
]
);
}






public function getPriority(): int
{
return -10;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound(Token::getCastTokenKinds());
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isCast()) {
continue;
}

$tokens[$index] = new Token([
$token->getId(),
strtr($token->getContent(), self::INSIDE_CAST_SPACE_REPLACE_MAP),
]);

if ('single' === $this->configuration['space']) {

if ($tokens[$index + 1]->isWhitespace(" \t")) {

$tokens[$index + 1] = new Token([T_WHITESPACE, ' ']);
} elseif (!$tokens[$index + 1]->isWhitespace()) {

$tokens->insertAt($index + 1, new Token([T_WHITESPACE, ' ']));
}

continue;
}


if ($tokens[$index + 1]->isWhitespace()) {
$tokens->clearAt($index + 1);
}
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('space', 'Spacing to apply between cast and variable.'))
->setAllowedValues(['none', 'single'])
->setDefault('single')
->getOption(),
]);
}
}
