<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;




final class NoNullPropertyInitializationFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Properties MUST not be explicitly initialized with `null` except when they have a type declaration (PHP 7.4).',
[
new CodeSample(
'<?php
class Foo {
    public $foo = null;
}
'
),
new CodeSample(
'<?php
class Foo {
    public static $foo = null;
}
'
),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound([T_CLASS, T_TRAIT]) && $tokens->isAnyTokenKindsFound([T_PUBLIC, T_PROTECTED, T_PRIVATE, T_VAR, T_STATIC]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$inClass = [];
$classLevel = 0;

for ($index = 0, $count = $tokens->count(); $index < $count; ++$index) {
if ($tokens[$index]->isGivenKind([T_CLASS, T_TRAIT])) { 
++$classLevel;
$inClass[$classLevel] = 1;

$index = $tokens->getNextTokenOfKind($index, ['{']);

continue;
}

if (0 === $classLevel) {
continue;
}

if ($tokens[$index]->equals('{')) {
++$inClass[$classLevel];

continue;
}

if ($tokens[$index]->equals('}')) {
--$inClass[$classLevel];

if (0 === $inClass[$classLevel]) {
unset($inClass[$classLevel]);
--$classLevel;
}

continue;
}


if (1 !== $inClass[$classLevel]) {
continue;
}

if (!$tokens[$index]->isGivenKind([T_PUBLIC, T_PROTECTED, T_PRIVATE, T_VAR, T_STATIC])) {
continue;
}

while (true) {
$varTokenIndex = $index = $tokens->getNextMeaningfulToken($index);

if ($tokens[$index]->isGivenKind(T_STATIC)) {
$varTokenIndex = $index = $tokens->getNextMeaningfulToken($index);
}

if (!$tokens[$index]->isGivenKind(T_VARIABLE)) {
break;
}

$index = $tokens->getNextMeaningfulToken($index);

if ($tokens[$index]->equals('=')) {
$index = $tokens->getNextMeaningfulToken($index);

if ($tokens[$index]->isGivenKind(T_NS_SEPARATOR)) {
$index = $tokens->getNextMeaningfulToken($index);
}

if ($tokens[$index]->equals([T_STRING, 'null'], false)) {
for ($i = $varTokenIndex + 1; $i <= $index; ++$i) {
if (
!($tokens[$i]->isWhitespace() && str_contains($tokens[$i]->getContent(), "\n"))
&& !$tokens[$i]->isComment()
) {
$tokens->clearAt($i);
}
}
}

++$index;
}

if (!$tokens[$index]->equals(',')) {
break;
}
}
}
}
}
