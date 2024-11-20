<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\PhpUnit;

use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\Fixer\AbstractPhpUnitFixer;
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
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use PhpCsFixer\Utils;

/**
@implements
@phpstan-type
@phpstan-type







*/
final class PhpUnitMethodCasingFixer extends AbstractPhpUnitFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




public const CAMEL_CASE = 'camel_case';




public const SNAKE_CASE = 'snake_case';

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Enforce camel (or snake) case for PHPUnit test methods, following configuration.',
[
new CodeSample(
'<?php
class MyTest extends \PhpUnit\FrameWork\TestCase
{
    public function test_my_code() {}
}
'
),
new CodeSample(
'<?php
class MyTest extends \PhpUnit\FrameWork\TestCase
{
    public function testMyCode() {}
}
',
['case' => self::SNAKE_CASE]
),
]
);
}






public function getPriority(): int
{
return 0;
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('case', 'Apply camel or snake case to test methods.'))
->setAllowedValues([self::CAMEL_CASE, self::SNAKE_CASE])
->setDefault(self::CAMEL_CASE)
->getOption(),
]);
}

protected function applyPhpUnitClassFix(Tokens $tokens, int $startIndex, int $endIndex): void
{
for ($index = $endIndex - 1; $index > $startIndex; --$index) {
if (!$this->isTestMethod($tokens, $index)) {
continue;
}

$functionNameIndex = $tokens->getNextMeaningfulToken($index);
$functionName = $tokens[$functionNameIndex]->getContent();
$newFunctionName = $this->updateMethodCasing($functionName);

if ($newFunctionName !== $functionName) {
$tokens[$functionNameIndex] = new Token([T_STRING, $newFunctionName]);
}

$docBlockIndex = $this->getDocBlockIndex($tokens, $index);

if ($this->isPHPDoc($tokens, $docBlockIndex)) {
$this->updateDocBlock($tokens, $docBlockIndex);
}
}
}

private function updateMethodCasing(string $functionName): string
{
$parts = explode('::', $functionName);

$functionNamePart = array_pop($parts);

if (self::CAMEL_CASE === $this->configuration['case']) {
$newFunctionNamePart = $functionNamePart;
$newFunctionNamePart = ucwords($newFunctionNamePart, '_');
$newFunctionNamePart = str_replace('_', '', $newFunctionNamePart);
$newFunctionNamePart = lcfirst($newFunctionNamePart);
} else {
$newFunctionNamePart = Utils::camelCaseToUnderscore($functionNamePart);
}

$parts[] = $newFunctionNamePart;

return implode('::', $parts);
}

private function isTestMethod(Tokens $tokens, int $index): bool
{

if (!$this->isMethod($tokens, $index)) {
return false;
}


$functionNameIndex = $tokens->getNextMeaningfulToken($index);
$functionName = $tokens[$functionNameIndex]->getContent();

if (str_starts_with($functionName, 'test')) {
return true;
}

$docBlockIndex = $this->getDocBlockIndex($tokens, $index);

return
$this->isPHPDoc($tokens, $docBlockIndex) 
&& str_contains($tokens[$docBlockIndex]->getContent(), '@test');
}

private function isMethod(Tokens $tokens, int $index): bool
{
$tokensAnalyzer = new TokensAnalyzer($tokens);

return $tokens[$index]->isGivenKind(T_FUNCTION) && !$tokensAnalyzer->isLambda($index);
}

private function updateDocBlock(Tokens $tokens, int $docBlockIndex): void
{
$doc = new DocBlock($tokens[$docBlockIndex]->getContent());
$lines = $doc->getLines();

$docBlockNeedsUpdate = false;
for ($inc = 0; $inc < \count($lines); ++$inc) {
$lineContent = $lines[$inc]->getContent();
if (!str_contains($lineContent, '@depends')) {
continue;
}

$newLineContent = Preg::replaceCallback('/(@depends\s+)(.+)(\b)/', fn (array $matches): string => \sprintf(
'%s%s%s',
$matches[1],
$this->updateMethodCasing($matches[2]),
$matches[3]
), $lineContent);

if ($newLineContent !== $lineContent) {
$lines[$inc] = new Line($newLineContent);
$docBlockNeedsUpdate = true;
}
}

if ($docBlockNeedsUpdate) {
$lines = implode('', $lines);
$tokens[$docBlockIndex] = new Token([T_DOC_COMMENT, $lines]);
}
}
}
