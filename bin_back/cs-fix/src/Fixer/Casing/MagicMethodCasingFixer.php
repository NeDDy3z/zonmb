<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Casing;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class MagicMethodCasingFixer extends AbstractFixer
{



private static array $magicNames = [
'__call' => '__call',
'__callstatic' => '__callStatic',
'__clone' => '__clone',
'__construct' => '__construct',
'__debuginfo' => '__debugInfo',
'__destruct' => '__destruct',
'__get' => '__get',
'__invoke' => '__invoke',
'__isset' => '__isset',
'__serialize' => '__serialize',
'__set' => '__set',
'__set_state' => '__set_state',
'__sleep' => '__sleep',
'__tostring' => '__toString',
'__unserialize' => '__unserialize',
'__unset' => '__unset',
'__wakeup' => '__wakeup',
];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Magic method definitions and calls must be using the correct casing.',
[
new CodeSample(
'<?php
class Foo
{
    public function __Sleep()
    {
    }
}
'
),
new CodeSample(
'<?php
$foo->__INVOKE(1);
'
),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_STRING) && $tokens->isAnyTokenKindsFound([T_FUNCTION, T_DOUBLE_COLON, ...Token::getObjectOperatorKinds()]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$inClass = 0;
$tokenCount = \count($tokens);

for ($index = 1; $index < $tokenCount - 2; ++$index) {
if (0 === $inClass && $tokens[$index]->isClassy()) {
$inClass = 1;
$index = $tokens->getNextTokenOfKind($index, ['{']);

continue;
}

if (0 !== $inClass) {
if ($tokens[$index]->equals('{')) {
++$inClass;

continue;
}

if ($tokens[$index]->equals('}')) {
--$inClass;

continue;
}
}

if (!$tokens[$index]->isGivenKind(T_STRING)) {
continue; 
}

$content = $tokens[$index]->getContent();

if (!str_starts_with($content, '__')) {
continue; 
}

$name = strtolower($content);

if (!$this->isMagicMethodName($name)) {
continue; 
}

$nameInCorrectCasing = $this->getMagicMethodNameInCorrectCasing($name);
if ($nameInCorrectCasing === $content) {
continue; 
}

if ($this->isFunctionSignature($tokens, $index)) {
if (0 !== $inClass) {

$this->setTokenToCorrectCasing($tokens, $index, $nameInCorrectCasing);
}

continue;
}

if ($this->isMethodCall($tokens, $index)) {
$this->setTokenToCorrectCasing($tokens, $index, $nameInCorrectCasing);

continue;
}

if (
('__callstatic' === $name || '__set_state' === $name)
&& $this->isStaticMethodCall($tokens, $index)
) {
$this->setTokenToCorrectCasing($tokens, $index, $nameInCorrectCasing);
}
}
}

private function isFunctionSignature(Tokens $tokens, int $index): bool
{
$prevIndex = $tokens->getPrevMeaningfulToken($index);
if (!$tokens[$prevIndex]->isGivenKind(T_FUNCTION)) {
return false; 
}

return $tokens[$tokens->getNextMeaningfulToken($index)]->equals('(');
}

private function isMethodCall(Tokens $tokens, int $index): bool
{
$prevIndex = $tokens->getPrevMeaningfulToken($index);
if (!$tokens[$prevIndex]->isObjectOperator()) {
return false; 
}

return $tokens[$tokens->getNextMeaningfulToken($index)]->equals('(');
}

private function isStaticMethodCall(Tokens $tokens, int $index): bool
{
$prevIndex = $tokens->getPrevMeaningfulToken($index);
if (!$tokens[$prevIndex]->isGivenKind(T_DOUBLE_COLON)) {
return false; 
}

return $tokens[$tokens->getNextMeaningfulToken($index)]->equals('(');
}

private function isMagicMethodName(string $name): bool
{
return isset(self::$magicNames[$name]);
}




private function getMagicMethodNameInCorrectCasing(string $name): string
{
return self::$magicNames[$name];
}

private function setTokenToCorrectCasing(Tokens $tokens, int $index, string $nameInCorrectCasing): void
{
$tokens[$index] = new Token([T_STRING, $nameInCorrectCasing]);
}
}
