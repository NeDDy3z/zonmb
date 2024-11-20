<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\AttributeAnalysis;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
@phpstan-import-type


*/
final class AttributeAnalyzer
{
private const TOKEN_KINDS_NOT_ALLOWED_IN_ATTRIBUTE = [
';',
'{',
[T_ATTRIBUTE],
[T_FUNCTION],
[T_OPEN_TAG],
[T_OPEN_TAG_WITH_ECHO],
[T_PRIVATE],
[T_PROTECTED],
[T_PUBLIC],
[T_RETURN],
[T_VARIABLE],
[CT::T_ATTRIBUTE_CLOSE],
];




public static function isAttribute(Tokens $tokens, int $index): bool
{
if (
!\defined('T_ATTRIBUTE') 
|| !$tokens[$index]->isGivenKind(T_STRING) 
|| !$tokens->isAnyTokenKindsFound([T_ATTRIBUTE]) 
) {
return false;
}

$attributeStartIndex = $tokens->getPrevTokenOfKind($index, self::TOKEN_KINDS_NOT_ALLOWED_IN_ATTRIBUTE);
if (!$tokens[$attributeStartIndex]->isGivenKind(T_ATTRIBUTE)) {
return false;
}


$count = 0;
for ($i = $attributeStartIndex + 1; $i < $index; ++$i) {
if ($tokens[$i]->equals('(')) {
++$count;
} elseif ($tokens[$i]->equals(')')) {
--$count;
}
}

return 0 === $count;
}






public static function collect(Tokens $tokens, int $index): array
{
if (!$tokens[$index]->isGivenKind(T_ATTRIBUTE)) {
throw new \InvalidArgumentException('Given index must point to an attribute.');
}


while ($tokens[$prevIndex = $tokens->getPrevMeaningfulToken($index)]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
$index = $tokens->findBlockStart(Tokens::BLOCK_TYPE_ATTRIBUTE, $prevIndex);
}


$elements = [];

$openingIndex = $index;
do {
$elements[] = $element = self::collectOne($tokens, $openingIndex);
$openingIndex = $tokens->getNextMeaningfulToken($element->getEndIndex());
} while ($tokens[$openingIndex]->isGivenKind(T_ATTRIBUTE));

return $elements;
}




public static function collectOne(Tokens $tokens, int $index): AttributeAnalysis
{
if (!$tokens[$index]->isGivenKind(T_ATTRIBUTE)) {
throw new \InvalidArgumentException('Given index must point to an attribute.');
}

$startIndex = $index;
$prevIndex = $tokens->getPrevMeaningfulToken($index);

if ($tokens[$tokens->getPrevMeaningfulToken($index)]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {

$startIndex = $tokens->getNextNonWhitespace($prevIndex);
}

$closingIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ATTRIBUTE, $index);
$endIndex = $tokens->getNextNonWhitespace($closingIndex);

return new AttributeAnalysis(
$startIndex,
$endIndex - 1,
$index,
$closingIndex,
self::collectAttributes($tokens, $index, $closingIndex),
);
}




private static function collectAttributes(Tokens $tokens, int $index, int $closingIndex): array
{

$elements = [];

do {
$attributeStartIndex = $index + 1;

$nameStartIndex = $tokens->getNextTokenOfKind($index, [[T_STRING], [T_NS_SEPARATOR]]);
$index = $tokens->getNextTokenOfKind($attributeStartIndex, ['(', ',', [CT::T_ATTRIBUTE_CLOSE]]);
$attributeName = $tokens->generatePartialCode($nameStartIndex, $tokens->getPrevMeaningfulToken($index));


if ($tokens[$index]->equals('(')) {
$index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
$index = $tokens->getNextTokenOfKind($index, [',', [CT::T_ATTRIBUTE_CLOSE]]);
}

$elements[] = [
'start' => $attributeStartIndex,
'end' => $index - 1,
'name' => $attributeName,
];

$nextIndex = $index;


if ($nextIndex < $closingIndex) {
$nextIndex = $tokens->getNextMeaningfulToken($index);
}
} while ($nextIndex < $closingIndex);


--$index;
while ($tokens[$index]->isWhitespace()) {
if (Preg::match('/\R/', $tokens[$index]->getContent())) {
$lastElementKey = array_key_last($elements);
$elements[$lastElementKey]['end'] = $index - 1;

break;
}
--$index;
}

return $elements;
}
}
