<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\StringNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
@implements
@phpstan-type
@phpstan-type







*/
final class SingleQuoteFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
$codeSample = <<<'EOF'
            <?php

            $a = "sample";
            $b = "sample with 'single-quotes'";

            EOF;

return new FixerDefinition(
'Convert double quotes to single quotes for simple strings.',
[
new CodeSample($codeSample),
new CodeSample(
$codeSample,
['strings_containing_single_quote_chars' => true]
),
]
);
}







public function getPriority(): int
{
return 10;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_CONSTANT_ENCAPSED_STRING);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_CONSTANT_ENCAPSED_STRING)) {
continue;
}

$content = $token->getContent();
$prefix = '';

if ('b' === strtolower($content[0])) {
$prefix = $content[0];
$content = substr($content, 1);
}

if (
'"' === $content[0]
&& (true === $this->configuration['strings_containing_single_quote_chars'] || !str_contains($content, "'"))

&& !Preg::match('/(?<!\\\)(?:\\\{2})*\\\(?!["$\\\])/', $content)
) {
$content = substr($content, 1, -1);
$content = str_replace(['\"', '\$', '\''], ['"', '$', '\\\''], $content);
$tokens[$index] = new Token([T_CONSTANT_ENCAPSED_STRING, $prefix.'\''.$content.'\'']);
}
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('strings_containing_single_quote_chars', 'Whether to fix double-quoted strings that contains single-quotes.'))
->setAllowedTypes(['bool'])
->setDefault(false)
->getOption(),
]);
}
}
