<?php

declare(strict_types=1);











namespace PhpCsFixer\Indicator;

use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Tokens;




final class PhpUnitTestCaseIndicator
{
public function isPhpUnitClass(Tokens $tokens, int $index): bool
{
if (!$tokens[$index]->isGivenKind(T_CLASS)) {
throw new \LogicException(\sprintf('No "T_CLASS" at given index %d, got "%s".', $index, $tokens[$index]->getName()));
}

$index = $tokens->getNextMeaningfulToken($index);

if (!$tokens[$index]->isGivenKind(T_STRING)) {
return false;
}

$extendsIndex = $tokens->getNextTokenOfKind($index, ['{', [T_EXTENDS]]);

if (!$tokens[$extendsIndex]->isGivenKind(T_EXTENDS)) {
return false;
}

if (Preg::match('/(?:Test|TestCase)$/', $tokens[$index]->getContent())) {
return true;
}

while (null !== $index = $tokens->getNextMeaningfulToken($index)) {
if ($tokens[$index]->equals('{')) {
break; 
}

if (!$tokens[$index]->isGivenKind(T_STRING)) {
continue; 
}

if (Preg::match('/(?:Test|TestCase)(?:Interface)?$/', $tokens[$index]->getContent())) {
return true;
}
}

return false;
}









public function findPhpUnitClasses(Tokens $tokens): iterable
{
for ($index = $tokens->count() - 1; $index > 0; --$index) {
if (!$tokens[$index]->isGivenKind(T_CLASS) || !$this->isPhpUnitClass($tokens, $index)) {
continue;
}

$startIndex = $tokens->getNextTokenOfKind($index, ['{']);

if (null === $startIndex) {
return;
}

$endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $startIndex);

yield [$startIndex, $endIndex];
}
}
}
