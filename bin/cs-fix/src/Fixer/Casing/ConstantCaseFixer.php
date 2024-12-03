<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Casing;

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
final class ConstantCaseFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;






private $fixFunction;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'The PHP constants `true`, `false`, and `null` MUST be written using the correct casing.',
[
new CodeSample("<?php\n\$a = FALSE;\n\$b = True;\n\$c = nuLL;\n"),
new CodeSample("<?php\n\$a = FALSE;\n\$b = True;\n\$c = nuLL;\n", ['case' => 'upper']),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_STRING);
}

protected function configurePostNormalisation(): void
{
if ('lower' === $this->configuration['case']) {
$this->fixFunction = static fn (string $content): string => strtolower($content);
}

if ('upper' === $this->configuration['case']) {
$this->fixFunction = static fn (string $content): string => strtoupper($content);
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('case', 'Whether to use the `upper` or `lower` case syntax.'))
->setAllowedValues(['upper', 'lower'])
->setDefault('lower')
->getOption(),
]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
static $forbiddenPrevKinds = null;
if (null === $forbiddenPrevKinds) {
$forbiddenPrevKinds = [
T_DOUBLE_COLON,
T_EXTENDS,
T_IMPLEMENTS,
T_INSTANCEOF,
T_NAMESPACE,
T_NEW,
T_NS_SEPARATOR,
...Token::getObjectOperatorKinds(),
];
}

foreach ($tokens as $index => $token) {
if (!$token->equalsAny([[T_STRING, 'true'], [T_STRING, 'false'], [T_STRING, 'null']], false)) {
continue;
}

$prevIndex = $tokens->getPrevMeaningfulToken($index);
if ($tokens[$prevIndex]->isGivenKind($forbiddenPrevKinds)) {
continue;
}

$nextIndex = $tokens->getNextMeaningfulToken($index);
if ($tokens[$nextIndex]->isGivenKind(T_PAAMAYIM_NEKUDOTAYIM) || $tokens[$nextIndex]->equalsAny(['='], false)) {
continue;
}

if ($tokens[$prevIndex]->isGivenKind(T_CASE) && $tokens[$nextIndex]->equals(';')) {
continue;
}

$tokens[$index] = new Token([$token->getId(), ($this->fixFunction)($token->getContent())]);
}
}
}
