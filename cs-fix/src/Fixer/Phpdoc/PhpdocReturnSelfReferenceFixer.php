<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\DocBlock;
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
use PhpCsFixer\Utils;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;

/**
@implements
@phpstan-type
@phpstan-type





*/
final class PhpdocReturnSelfReferenceFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




private static array $toTypes = [
'$this',
'static',
'self',
];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'The type of `@return` annotations of methods returning a reference to itself must the configured one.',
[
new CodeSample(
'<?php
class Sample
{
    /**
     * @return this
     */
    public function test1()
    {
        return $this;
    }

    /**
     * @return $self
     */
    public function test2()
    {
        return $this;
    }
}
'
),
new CodeSample(
'<?php
class Sample
{
    /**
     * @return this
     */
    public function test1()
    {
        return $this;
    }

    /**
     * @return $self
     */
    public function test2()
    {
        return $this;
    }
}
',
['replacements' => ['this' => 'self']]
),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return \count($tokens) > 10 && $tokens->isAllTokenKindsFound([T_DOC_COMMENT, T_FUNCTION]) && $tokens->isAnyTokenKindsFound(Token::getClassyTokenKinds());
}







public function getPriority(): int
{
return 10;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$tokensAnalyzer = new TokensAnalyzer($tokens);

foreach ($tokensAnalyzer->getClassyElements() as $index => $element) {
if ('method' === $element['type']) {
$this->fixMethod($tokens, $index);
}
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
$default = [
'this' => '$this',
'@this' => '$this',
'$self' => 'self',
'@self' => 'self',
'$static' => 'static',
'@static' => 'static',
];

return new FixerConfigurationResolver([
(new FixerOptionBuilder('replacements', 'Mapping between replaced return types with new ones.'))
->setAllowedTypes(['array<string, string>'])
->setNormalizer(static function (Options $options, array $value) use ($default): array {
$normalizedValue = [];

foreach ($value as $from => $to) {
if (\is_string($from)) {
$from = strtolower($from);
}

if (!isset($default[$from])) {
throw new InvalidOptionsException(\sprintf(
'Unknown key "%s", expected any of %s.',
\gettype($from).'#'.$from,
Utils::naturalLanguageJoin(array_keys($default))
));
}

if (!\in_array($to, self::$toTypes, true)) {
throw new InvalidOptionsException(\sprintf(
'Unknown value "%s", expected any of %s.',
\is_object($to) ? \get_class($to) : \gettype($to).(\is_resource($to) ? '' : '#'.$to),
Utils::naturalLanguageJoin(self::$toTypes)
));
}

$normalizedValue[$from] = $to;
}

return $normalizedValue;
})
->setDefault($default)
->getOption(),
]);
}

private function fixMethod(Tokens $tokens, int $index): void
{
static $methodModifiers = [T_STATIC, T_FINAL, T_ABSTRACT, T_PRIVATE, T_PROTECTED, T_PUBLIC];


while (true) {
$tokenIndex = $tokens->getPrevMeaningfulToken($index);
if (!$tokens[$tokenIndex]->isGivenKind($methodModifiers)) {
break;
}

$index = $tokenIndex;
}

$docIndex = $tokens->getPrevNonWhitespace($index);
if (!$tokens[$docIndex]->isGivenKind(T_DOC_COMMENT)) {
return;
}


$docBlock = new DocBlock($tokens[$docIndex]->getContent());
$returnsBlock = $docBlock->getAnnotationsOfType('return');

if (0 === \count($returnsBlock)) {
return; 
}

$returnsBlock = $returnsBlock[0];
$types = $returnsBlock->getTypes();

if (0 === \count($types)) {
return; 
}

$newTypes = [];

foreach ($types as $type) {
$newTypes[] = $this->configuration['replacements'][strtolower($type)] ?? $type;
}

if ($types === $newTypes) {
return;
}

$returnsBlock->setTypes($newTypes);
$tokens[$docIndex] = new Token([T_DOC_COMMENT, $docBlock->getContent()]);
}
}
