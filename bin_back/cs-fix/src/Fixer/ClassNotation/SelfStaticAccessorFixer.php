<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

final class SelfStaticAccessorFixer extends AbstractFixer
{
private TokensAnalyzer $tokensAnalyzer;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Inside an enum or `final`/anonymous class, `self` should be preferred over `static`.',
[
new CodeSample(
'<?php
final class Sample
{
    private static $A = 1;

    public function getBar()
    {
        return static::class.static::test().static::$A;
    }

    private static function test()
    {
        return \'test\';
    }
}
'
),
new CodeSample(
'<?php
final class Foo
{
    public function bar()
    {
        return new static();
    }
}
'
),
new CodeSample(
'<?php
final class Foo
{
    public function isBar()
    {
        return $foo instanceof static;
    }
}
'
),
new CodeSample(
'<?php
$a = new class() {
    public function getBar()
    {
        return static::class;
    }
};
'
),
new VersionSpecificCodeSample(
'<?php
enum Foo
{
    public const A = 123;

    public static function bar(): void
    {
        echo static::A;
    }
}
',
new VersionSpecification(8_01_00)
),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
$classyTypes = [T_CLASS];

if (\defined('T_ENUM')) { 
$classyTypes[] = T_ENUM;
}

return $tokens->isTokenKindFound(T_STATIC)
&& $tokens->isAnyTokenKindsFound($classyTypes)
&& $tokens->isAnyTokenKindsFound([T_DOUBLE_COLON, T_NEW, T_INSTANCEOF]);
}






public function getPriority(): int
{
return -10;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$classyTokensOfInterest = [[T_CLASS]];

if (\defined('T_ENUM')) {
$classyTokensOfInterest[] = [T_ENUM]; 
}

$this->tokensAnalyzer = new TokensAnalyzer($tokens);
$classyIndex = $tokens->getNextTokenOfKind(0, $classyTokensOfInterest);

while (null !== $classyIndex) {
if ($tokens[$classyIndex]->isGivenKind(T_CLASS)) {
$modifiers = $this->tokensAnalyzer->getClassyModifiers($classyIndex);

if (
isset($modifiers['final'])
|| $this->tokensAnalyzer->isAnonymousClass($classyIndex)
) {
$classyIndex = $this->fixClassy($tokens, $classyIndex);
}
} else {
$classyIndex = $this->fixClassy($tokens, $classyIndex);
}

$classyIndex = $tokens->getNextTokenOfKind($classyIndex, $classyTokensOfInterest);
}
}

private function fixClassy(Tokens $tokens, int $index): int
{
$index = $tokens->getNextTokenOfKind($index, ['{']);
$classOpenCount = 1;

while ($classOpenCount > 0) {
++$index;

if ($tokens[$index]->equals('{')) {
++$classOpenCount;

continue;
}

if ($tokens[$index]->equals('}')) {
--$classOpenCount;

continue;
}

if ($tokens[$index]->isGivenKind(T_FUNCTION)) {

if ($this->tokensAnalyzer->isLambda($index)) {

$index = $tokens->getNextTokenOfKind($index, ['{']);
$openCount = 1;

do {
$index = $tokens->getNextTokenOfKind($index, ['}', '{', [T_CLASS]]);
if ($tokens[$index]->equals('}')) {
--$openCount;
} elseif ($tokens[$index]->equals('{')) {
++$openCount;
} else {
$index = $this->fixClassy($tokens, $index);
}
} while ($openCount > 0);
}

continue;
}

if ($tokens[$index]->isGivenKind([T_NEW, T_INSTANCEOF])) {
$index = $tokens->getNextMeaningfulToken($index);

if ($tokens[$index]->isGivenKind(T_STATIC)) {
$tokens[$index] = new Token([T_STRING, 'self']);
}

continue;
}

if (!$tokens[$index]->isGivenKind(T_STATIC)) {
continue;
}

$staticIndex = $index;
$index = $tokens->getNextMeaningfulToken($index);

if (!$tokens[$index]->isGivenKind(T_DOUBLE_COLON)) {
continue;
}

$tokens[$staticIndex] = new Token([T_STRING, 'self']);
}

return $index;
}
}
