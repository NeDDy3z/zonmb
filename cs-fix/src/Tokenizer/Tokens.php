<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer;

use PhpCsFixer\Console\Application;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use PhpCsFixer\Utils;

/**
@extends












*/
class Tokens extends \SplFixedArray
{
public const BLOCK_TYPE_PARENTHESIS_BRACE = 1;
public const BLOCK_TYPE_CURLY_BRACE = 2;
public const BLOCK_TYPE_INDEX_SQUARE_BRACE = 3;
public const BLOCK_TYPE_ARRAY_SQUARE_BRACE = 4;
public const BLOCK_TYPE_DYNAMIC_PROP_BRACE = 5;
public const BLOCK_TYPE_DYNAMIC_VAR_BRACE = 6;
public const BLOCK_TYPE_ARRAY_INDEX_CURLY_BRACE = 7;
public const BLOCK_TYPE_GROUP_IMPORT_BRACE = 8;
public const BLOCK_TYPE_DESTRUCTURING_SQUARE_BRACE = 9;
public const BLOCK_TYPE_BRACE_CLASS_INSTANTIATION = 10;
public const BLOCK_TYPE_ATTRIBUTE = 11;
public const BLOCK_TYPE_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS = 12;
public const BLOCK_TYPE_DYNAMIC_CLASS_CONSTANT_FETCH_CURLY_BRACE = 13;
public const BLOCK_TYPE_COMPLEX_STRING_VARIABLE = 14;






private static array $cache = [];






private array $blockStartCache = [];






private array $blockEndCache = [];






private ?string $codeHash = null;






private bool $changed = false;










private array $foundTokenKinds = [];




private ?array $namespaceDeclarations = null;




public function __clone()
{
foreach ($this as $key => $val) {
$this[$key] = clone $val;
}
}






public static function clearCache(?string $key = null): void
{
if (null === $key) {
self::$cache = [];

return;
}

unset(self::$cache[$key]);
}






public static function detectBlockType(Token $token): ?array
{
static $blockEdgeKinds = null;

if (null === $blockEdgeKinds) {
$blockEdgeKinds = [];
foreach (self::getBlockEdgeDefinitions() as $type => $definition) {
$blockEdgeKinds[
\is_string($definition['start']) ? $definition['start'] : $definition['start'][0]
] = ['type' => $type, 'isStart' => true];
$blockEdgeKinds[
\is_string($definition['end']) ? $definition['end'] : $definition['end'][0]
] = ['type' => $type, 'isStart' => false];
}
}



$tokenKind = $token->isArray() ? $token->getId() : $token->getContent();

return $blockEdgeKinds[$tokenKind] ?? null;
}







public static function fromArray($array, $saveIndices = null): self
{
$tokens = new self(\count($array));

if (false !== $saveIndices && !array_is_list($array)) {
Utils::triggerDeprecation(new \InvalidArgumentException(\sprintf(
'Parameter "array" should be a list. This will be enforced in version %d.0.',
Application::getMajorVersion() + 1
)));

foreach ($array as $key => $val) {
$tokens[$key] = $val;
}
} else {
$index = 0;
foreach ($array as $val) {
$tokens[$index++] = $val;
}
}

$tokens->generateCode(); 
$tokens->clearChanged();

return $tokens;
}






public static function fromCode(string $code): self
{
$codeHash = self::calculateCodeHash($code);

if (self::hasCache($codeHash)) {
$tokens = self::getCache($codeHash);


$tokens->generateCode();

if ($codeHash === $tokens->codeHash) {
$tokens->clearEmptyTokens();
$tokens->clearChanged();

return $tokens;
}
}

$tokens = new self();
$tokens->setCode($code);
$tokens->clearChanged();

return $tokens;
}




public static function getBlockEdgeDefinitions(): array
{
static $definitions = null;
if (null === $definitions) {
$definitions = [
self::BLOCK_TYPE_CURLY_BRACE => [
'start' => '{',
'end' => '}',
],
self::BLOCK_TYPE_PARENTHESIS_BRACE => [
'start' => '(',
'end' => ')',
],
self::BLOCK_TYPE_INDEX_SQUARE_BRACE => [
'start' => '[',
'end' => ']',
],
self::BLOCK_TYPE_ARRAY_SQUARE_BRACE => [
'start' => [CT::T_ARRAY_SQUARE_BRACE_OPEN, '['],
'end' => [CT::T_ARRAY_SQUARE_BRACE_CLOSE, ']'],
],
self::BLOCK_TYPE_DYNAMIC_PROP_BRACE => [
'start' => [CT::T_DYNAMIC_PROP_BRACE_OPEN, '{'],
'end' => [CT::T_DYNAMIC_PROP_BRACE_CLOSE, '}'],
],
self::BLOCK_TYPE_DYNAMIC_VAR_BRACE => [
'start' => [CT::T_DYNAMIC_VAR_BRACE_OPEN, '{'],
'end' => [CT::T_DYNAMIC_VAR_BRACE_CLOSE, '}'],
],
self::BLOCK_TYPE_ARRAY_INDEX_CURLY_BRACE => [
'start' => [CT::T_ARRAY_INDEX_CURLY_BRACE_OPEN, '{'],
'end' => [CT::T_ARRAY_INDEX_CURLY_BRACE_CLOSE, '}'],
],
self::BLOCK_TYPE_GROUP_IMPORT_BRACE => [
'start' => [CT::T_GROUP_IMPORT_BRACE_OPEN, '{'],
'end' => [CT::T_GROUP_IMPORT_BRACE_CLOSE, '}'],
],
self::BLOCK_TYPE_DESTRUCTURING_SQUARE_BRACE => [
'start' => [CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN, '['],
'end' => [CT::T_DESTRUCTURING_SQUARE_BRACE_CLOSE, ']'],
],
self::BLOCK_TYPE_BRACE_CLASS_INSTANTIATION => [
'start' => [CT::T_BRACE_CLASS_INSTANTIATION_OPEN, '('],
'end' => [CT::T_BRACE_CLASS_INSTANTIATION_CLOSE, ')'],
],
self::BLOCK_TYPE_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS => [
'start' => [CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN, '('],
'end' => [CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE, ')'],
],
self::BLOCK_TYPE_DYNAMIC_CLASS_CONSTANT_FETCH_CURLY_BRACE => [
'start' => [CT::T_DYNAMIC_CLASS_CONSTANT_FETCH_CURLY_BRACE_OPEN, '{'],
'end' => [CT::T_DYNAMIC_CLASS_CONSTANT_FETCH_CURLY_BRACE_CLOSE, '}'],
],
self::BLOCK_TYPE_COMPLEX_STRING_VARIABLE => [
'start' => [T_DOLLAR_OPEN_CURLY_BRACES, '${'],
'end' => [CT::T_DOLLAR_CLOSE_CURLY_BRACES, '}'],
],
];


if (\defined('T_ATTRIBUTE')) {
$definitions[self::BLOCK_TYPE_ATTRIBUTE] = [
'start' => [T_ATTRIBUTE, '#['],
'end' => [CT::T_ATTRIBUTE_CLOSE, ']'],
];
}
}

return $definitions;
}






#[\ReturnTypeWillChange]
public function setSize($size): bool
{
if (\count($this) !== $size) {
$this->changed = true;
$this->namespaceDeclarations = null;

return parent::setSize($size);
}

return true;
}






public function offsetUnset($index): void
{
if (\count($this) - 1 !== $index) {
Utils::triggerDeprecation(new \InvalidArgumentException(\sprintf(
'Tokens should be a list - only the last index can be unset. This will be enforced in version %d.0.',
Application::getMajorVersion() + 1
)));
}

if (isset($this[$index])) {
if (isset($this->blockStartCache[$index])) {
unset($this->blockEndCache[$this->blockStartCache[$index]], $this->blockStartCache[$index]);
}
if (isset($this->blockEndCache[$index])) {
unset($this->blockStartCache[$this->blockEndCache[$index]], $this->blockEndCache[$index]);
}

$this->unregisterFoundToken($this[$index]);

$this->changed = true;
$this->namespaceDeclarations = null;
}

parent::offsetUnset($index);
}









public function offsetSet($index, $newval): void
{
if (0 > $index || \count($this) <= $index) {
Utils::triggerDeprecation(new \InvalidArgumentException(\sprintf(
'Tokens should be a list - index must be within the existing range. This will be enforced in version %d.0.',
Application::getMajorVersion() + 1
)));
}

if (!isset($this[$index]) || !$this[$index]->equals($newval)) {
if (isset($this[$index])) {
if (isset($this->blockStartCache[$index])) {
unset($this->blockEndCache[$this->blockStartCache[$index]], $this->blockStartCache[$index]);
}
if (isset($this->blockEndCache[$index])) {
unset($this->blockStartCache[$this->blockEndCache[$index]], $this->blockEndCache[$index]);
}

$this->unregisterFoundToken($this[$index]);
}

$this->changed = true;
$this->namespaceDeclarations = null;

$this->registerFoundToken($newval);
}

parent::offsetSet($index, $newval);
}




public function clearChanged(): void
{
$this->changed = false;
}






public function clearEmptyTokens(): void
{
$limit = \count($this);

for ($index = 0; $index < $limit; ++$index) {
if ($this->isEmptyAt($index)) {
break;
}
}


if ($limit === $index) {
return;
}

for ($count = $index; $index < $limit; ++$index) {
if (!$this->isEmptyAt($index)) {

parent::offsetSet($count++, $this[$index]);
}
}


if (!$this->changed) {

throw new \LogicException('Unexpected non-changed collection with _EMPTY_ Tokens. Fix the code!');
}


$this->namespaceDeclarations = null;
$this->blockStartCache = [];
$this->blockEndCache = [];

$this->setSize($count);
}













public function ensureWhitespaceAtIndex(int $index, int $indexOffset, string $whitespace): bool
{
$removeLastCommentLine = static function (self $tokens, int $index, int $indexOffset, string $whitespace): string {
$token = $tokens[$index];

if (1 === $indexOffset && $token->isGivenKind(T_OPEN_TAG)) {
if (str_starts_with($whitespace, "\r\n")) {
$tokens[$index] = new Token([T_OPEN_TAG, rtrim($token->getContent())."\r\n"]);

return \strlen($whitespace) > 2 
? substr($whitespace, 2)
: '';
}

$tokens[$index] = new Token([T_OPEN_TAG, rtrim($token->getContent()).$whitespace[0]]);

return \strlen($whitespace) > 1 
? substr($whitespace, 1)
: '';
}

return $whitespace;
};

if ($this[$index]->isWhitespace()) {
$whitespace = $removeLastCommentLine($this, $index - 1, $indexOffset, $whitespace);

if ('' === $whitespace) {
$this->clearAt($index);
} else {
$this[$index] = new Token([T_WHITESPACE, $whitespace]);
}

return false;
}

$whitespace = $removeLastCommentLine($this, $index, $indexOffset, $whitespace);

if ('' === $whitespace) {
return false;
}

$this->insertAt(
$index + $indexOffset,
[new Token([T_WHITESPACE, $whitespace])]
);

return true;
}







public function findBlockEnd(int $type, int $searchIndex): int
{
return $this->findOppositeBlockEdge($type, $searchIndex, true);
}







public function findBlockStart(int $type, int $searchIndex): int
{
return $this->findOppositeBlockEdge($type, $searchIndex, false);
}








public function findGivenKind($possibleKind, int $start = 0, ?int $end = null): array
{
if (null === $end) {
$end = \count($this);
}

$elements = [];
$possibleKinds = (array) $possibleKind;

foreach ($possibleKinds as $kind) {
$elements[$kind] = [];
}

$possibleKinds = array_filter($possibleKinds, fn ($kind): bool => $this->isTokenKindFound($kind));

if (\count($possibleKinds) > 0) {
for ($i = $start; $i < $end; ++$i) {
$token = $this[$i];
if ($token->isGivenKind($possibleKinds)) {
$elements[$token->getId()][$i] = $token;
}
}
}

return \is_array($possibleKind) ? $elements : $elements[$possibleKind];
}

public function generateCode(): string
{
$code = $this->generatePartialCode(0, \count($this) - 1);
$this->changeCodeHash(self::calculateCodeHash($code));

return $code;
}







public function generatePartialCode(int $start, int $end): string
{
$code = '';

for ($i = $start; $i <= $end; ++$i) {
$code .= $this[$i]->getContent();
}

return $code;
}




public function getCodeHash(): string
{
return $this->codeHash;
}









public function getNextNonWhitespace(int $index, ?string $whitespaces = null): ?int
{
return $this->getNonWhitespaceSibling($index, 1, $whitespaces);
}










public function getNextTokenOfKind(int $index, array $tokens = [], bool $caseSensitive = true): ?int
{
return $this->getTokenOfKindSibling($index, 1, $tokens, $caseSensitive);
}








public function getNonWhitespaceSibling(int $index, int $direction, ?string $whitespaces = null): ?int
{
while (true) {
$index += $direction;
if (!$this->offsetExists($index)) {
return null;
}

if (!$this[$index]->isWhitespace($whitespaces)) {
return $index;
}
}
}









public function getPrevNonWhitespace(int $index, ?string $whitespaces = null): ?int
{
return $this->getNonWhitespaceSibling($index, -1, $whitespaces);
}









public function getPrevTokenOfKind(int $index, array $tokens = [], bool $caseSensitive = true): ?int
{
return $this->getTokenOfKindSibling($index, -1, $tokens, $caseSensitive);
}









public function getTokenOfKindSibling(int $index, int $direction, array $tokens = [], bool $caseSensitive = true): ?int
{
$tokens = array_filter($tokens, fn ($token): bool => $this->isTokenKindFound($this->extractTokenKind($token)));

if (0 === \count($tokens)) {
return null;
}

while (true) {
$index += $direction;
if (!$this->offsetExists($index)) {
return null;
}

if ($this[$index]->equalsAny($tokens, $caseSensitive)) {
return $index;
}
}
}








public function getTokenNotOfKindSibling(int $index, int $direction, array $tokens = []): ?int
{
return $this->getTokenNotOfKind(
$index,
$direction,
fn (int $a): bool => $this[$a]->equalsAny($tokens),
);
}








public function getTokenNotOfKindsSibling(int $index, int $direction, array $kinds = []): ?int
{
return $this->getTokenNotOfKind(
$index,
$direction,
fn (int $index): bool => $this[$index]->isGivenKind($kinds),
);
}







public function getMeaningfulTokenSibling(int $index, int $direction): ?int
{
return $this->getTokenNotOfKindsSibling(
$index,
$direction,
[T_WHITESPACE, T_COMMENT, T_DOC_COMMENT]
);
}







public function getNonEmptySibling(int $index, int $direction): ?int
{
while (true) {
$index += $direction;
if (!$this->offsetExists($index)) {
return null;
}

if (!$this->isEmptyAt($index)) {
return $index;
}
}
}






public function getNextMeaningfulToken(int $index): ?int
{
return $this->getMeaningfulTokenSibling($index, 1);
}






public function getPrevMeaningfulToken(int $index): ?int
{
return $this->getMeaningfulTokenSibling($index, -1);
}













public function findSequence(array $sequence, int $start = 0, ?int $end = null, $caseSensitive = true): ?array
{
$sequenceCount = \count($sequence);
if (0 === $sequenceCount) {
throw new \InvalidArgumentException('Invalid sequence.');
}


$end = null === $end ? \count($this) - 1 : min($end, \count($this) - 1);

if ($start + $sequenceCount - 1 > $end) {
return null;
}

$nonMeaningFullKind = [T_COMMENT, T_DOC_COMMENT, T_WHITESPACE];


foreach ($sequence as $key => $token) {

if (!$token instanceof Token) {
if (\is_array($token) && !isset($token[1])) {


$token[1] = 'DUMMY';
}

$token = new Token($token);
}

if ($token->isGivenKind($nonMeaningFullKind)) {
throw new \InvalidArgumentException(\sprintf('Non-meaningful token at position: "%s".', $key));
}

if ('' === $token->getContent()) {
throw new \InvalidArgumentException(\sprintf('Non-meaningful (empty) token at position: "%s".', $key));
}
}

foreach ($sequence as $token) {
if (!$this->isTokenKindFound($this->extractTokenKind($token))) {
return null;
}
}



$firstKey = array_key_first($sequence);
$firstCs = self::isKeyCaseSensitive($caseSensitive, $firstKey);
$firstToken = $sequence[$firstKey];
unset($sequence[$firstKey]);


$index = $start - 1;
while ($index <= $end) {
$index = $this->getNextTokenOfKind($index, [$firstToken], $firstCs);


if (null === $index || $index > $end) {
return null;
}


$result = [$index => $this[$index]];


$currIdx = $index;


foreach ($sequence as $key => $token) {
$currIdx = $this->getNextMeaningfulToken($currIdx);


if (null === $currIdx || $currIdx > $end) {
return null;
}

if (!$this[$currIdx]->equals($token, self::isKeyCaseSensitive($caseSensitive, $key))) {

continue 2;
}


$result[$currIdx] = $this[$currIdx];
}



if (\count($sequence) < \count($result)) {
return $result;
}
}

return null;
}







public function insertAt(int $index, $items): void
{
$this->insertSlices([$index => $items]);
}




















public function insertSlices(array $slices): void
{
$itemsCount = 0;

foreach ($slices as $slice) {
$itemsCount += \is_array($slice) || $slice instanceof self ? \count($slice) : 1;
}

if (0 === $itemsCount) {
return;
}

$oldSize = \count($this);
$this->changed = true;
$this->namespaceDeclarations = null;
$this->blockStartCache = [];
$this->blockEndCache = [];
$this->setSize($oldSize + $itemsCount);

krsort($slices);
$farthestSliceIndex = array_key_first($slices);


if (!\is_int($farthestSliceIndex) || $farthestSliceIndex > $oldSize) {
throw new \OutOfBoundsException(\sprintf('Cannot insert index "%s" outside of collection.', $farthestSliceIndex));
}

$previousSliceIndex = $oldSize;



foreach ($slices as $index => $slice) {
if (!\is_int($index) || $index < 0) {
throw new \OutOfBoundsException(\sprintf('Invalid index "%s".', $index));
}

$slice = \is_array($slice) || $slice instanceof self ? $slice : [$slice];
$sliceCount = \count($slice);

for ($i = $previousSliceIndex - 1; $i >= $index; --$i) {
parent::offsetSet($i + $itemsCount, $this[$i]);
}

$previousSliceIndex = $index;
$itemsCount -= $sliceCount;

foreach ($slice as $indexItem => $item) {
if ('' === $item->getContent()) {
throw new \InvalidArgumentException('Must not add empty token to collection.');
}

$this->registerFoundToken($item);

parent::offsetSet($index + $itemsCount + $indexItem, $item);
}
}
}




public function isChanged(): bool
{
return $this->changed;
}

public function isEmptyAt(int $index): bool
{
$token = $this[$index];

return null === $token->getId() && '' === $token->getContent();
}

public function clearAt(int $index): void
{
$this[$index] = new Token('');
}








public function overrideRange(int $indexStart, int $indexEnd, iterable $items): void
{
$indexToChange = $indexEnd - $indexStart + 1;
$itemsCount = \count($items);



if ($itemsCount > $indexToChange) {
$placeholders = [];

while ($itemsCount > $indexToChange) {
$placeholders[] = new Token('__PLACEHOLDER__');
++$indexToChange;
}

$this->insertAt($indexEnd + 1, $placeholders);
}


foreach ($items as $itemIndex => $item) {
$this[$indexStart + $itemIndex] = $item;
}



if ($itemsCount < $indexToChange) {
$this->clearRange($indexStart + $itemsCount, $indexEnd);
}
}




public function removeLeadingWhitespace(int $index, ?string $whitespaces = null): void
{
$this->removeWhitespaceSafely($index, -1, $whitespaces);
}




public function removeTrailingWhitespace(int $index, ?string $whitespaces = null): void
{
$this->removeWhitespaceSafely($index, 1, $whitespaces);
}






public function setCode(string $code): void
{


if ($code === $this->generateCode()) {
return;
}


$this->setSize(0);
$this->blockStartCache = [];
$this->blockEndCache = [];

$tokens = token_get_all($code, TOKEN_PARSE);

$this->setSize(\count($tokens));

foreach ($tokens as $index => $token) {
$this[$index] = new Token($token);
}

$this->applyTransformers();

$this->foundTokenKinds = [];

foreach ($this as $token) {
$this->registerFoundToken($token);
}

if (\PHP_VERSION_ID < 8_00_00) {
$this->rewind();
}

$this->changeCodeHash(self::calculateCodeHash($code));
$this->changed = true;
$this->namespaceDeclarations = null;
}

public function toJson(): string
{
$output = new \SplFixedArray(\count($this));

foreach ($this as $index => $token) {
$output[$index] = $token->toArray();
}

if (\PHP_VERSION_ID < 8_00_00) {
$this->rewind();
}

return json_encode($output, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
}






public function isAllTokenKindsFound(array $tokenKinds): bool
{
foreach ($tokenKinds as $tokenKind) {
if (!isset($this->foundTokenKinds[$tokenKind])) {
return false;
}
}

return true;
}






public function isAnyTokenKindsFound(array $tokenKinds): bool
{
foreach ($tokenKinds as $tokenKind) {
if (isset($this->foundTokenKinds[$tokenKind])) {
return true;
}
}

return false;
}






public function isTokenKindFound($tokenKind): bool
{
return isset($this->foundTokenKinds[$tokenKind]);
}




public function countTokenKind($tokenKind): int
{
return $this->foundTokenKinds[$tokenKind] ?? 0;
}




public function clearRange(int $indexStart, int $indexEnd): void
{
for ($i = $indexStart; $i <= $indexEnd; ++$i) {
$this->clearAt($i);
}
}







public function isMonolithicPhp(): bool
{
if (1 !== ($this->countTokenKind(T_OPEN_TAG) + $this->countTokenKind(T_OPEN_TAG_WITH_ECHO))) {
return false;
}

return 0 === $this->countTokenKind(T_INLINE_HTML)
|| (1 === $this->countTokenKind(T_INLINE_HTML) && Preg::match('/^#!.+$/', $this[0]->getContent()));
}





public function isPartialCodeMultiline(int $start, int $end): bool
{
for ($i = $start; $i <= $end; ++$i) {
if (str_contains($this[$i]->getContent(), "\n")) {
return true;
}
}

return false;
}

public function hasAlternativeSyntax(): bool
{
return $this->isAnyTokenKindsFound([
T_ENDDECLARE,
T_ENDFOR,
T_ENDFOREACH,
T_ENDIF,
T_ENDSWITCH,
T_ENDWHILE,
]);
}

public function clearTokenAndMergeSurroundingWhitespace(int $index): void
{
$count = \count($this);
$this->clearAt($index);

if ($index === $count - 1) {
return;
}

$nextIndex = $this->getNonEmptySibling($index, 1);

if (null === $nextIndex || !$this[$nextIndex]->isWhitespace()) {
return;
}

$prevIndex = $this->getNonEmptySibling($index, -1);

if ($this[$prevIndex]->isWhitespace()) {
$this[$prevIndex] = new Token([T_WHITESPACE, $this[$prevIndex]->getContent().$this[$nextIndex]->getContent()]);
} elseif ($this->isEmptyAt($prevIndex + 1)) {
$this[$prevIndex + 1] = new Token([T_WHITESPACE, $this[$nextIndex]->getContent()]);
}

$this->clearAt($nextIndex);
}






public function getNamespaceDeclarations(): array
{
if (null === $this->namespaceDeclarations) {
$this->namespaceDeclarations = (new NamespacesAnalyzer())->getDeclarations($this);
}

return $this->namespaceDeclarations;
}




protected function applyTransformers(): void
{
$transformers = Transformers::createSingleton();
$transformers->transform($this);
}




private function removeWhitespaceSafely(int $index, int $direction, ?string $whitespaces = null): void
{
$whitespaceIndex = $this->getNonEmptySibling($index, $direction);
if (isset($this[$whitespaceIndex]) && $this[$whitespaceIndex]->isWhitespace()) {
$newContent = '';
$tokenToCheck = $this[$whitespaceIndex];


if (isset($this[$whitespaceIndex - 1]) && $this[$whitespaceIndex - 1]->isComment() && !str_starts_with($this[$whitespaceIndex - 1]->getContent(), '/*')) {
[, $newContent, $whitespacesToCheck] = Preg::split('/^(\R)/', $this[$whitespaceIndex]->getContent(), -1, PREG_SPLIT_DELIM_CAPTURE);

if ('' === $whitespacesToCheck) {
return;
}

$tokenToCheck = new Token([T_WHITESPACE, $whitespacesToCheck]);
}

if (!$tokenToCheck->isWhitespace($whitespaces)) {
return;
}

if ('' === $newContent) {
$this->clearAt($whitespaceIndex);
} else {
$this[$whitespaceIndex] = new Token([T_WHITESPACE, $newContent]);
}
}
}








private function findOppositeBlockEdge(int $type, int $searchIndex, bool $findEnd): int
{
$blockEdgeDefinitions = self::getBlockEdgeDefinitions();

if (!isset($blockEdgeDefinitions[$type])) {
throw new \InvalidArgumentException(\sprintf('Invalid param type: "%s".', $type));
}

if ($findEnd && isset($this->blockStartCache[$searchIndex])) {
return $this->blockStartCache[$searchIndex];
}

if (!$findEnd && isset($this->blockEndCache[$searchIndex])) {
return $this->blockEndCache[$searchIndex];
}

$startEdge = $blockEdgeDefinitions[$type]['start'];
$endEdge = $blockEdgeDefinitions[$type]['end'];
$startIndex = $searchIndex;
$endIndex = \count($this) - 1;
$indexOffset = 1;

if (!$findEnd) {
[$startEdge, $endEdge] = [$endEdge, $startEdge];
$indexOffset = -1;
$endIndex = 0;
}

if (!$this[$startIndex]->equals($startEdge)) {
throw new \InvalidArgumentException(\sprintf('Invalid param $startIndex - not a proper block "%s".', $findEnd ? 'start' : 'end'));
}

$blockLevel = 0;

for ($index = $startIndex; $index !== $endIndex; $index += $indexOffset) {
$token = $this[$index];

if ($token->equals($startEdge)) {
++$blockLevel;

continue;
}

if ($token->equals($endEdge)) {
--$blockLevel;

if (0 === $blockLevel) {
break;
}
}
}

if (!$this[$index]->equals($endEdge)) {
throw new \UnexpectedValueException(\sprintf('Missing block "%s".', $findEnd ? 'end' : 'start'));
}

if ($startIndex < $index) {
$this->blockStartCache[$startIndex] = $index;
$this->blockEndCache[$index] = $startIndex;
} else {
$this->blockStartCache[$index] = $startIndex;
$this->blockEndCache[$startIndex] = $index;
}

return $index;
}






private static function calculateCodeHash(string $code): string
{
return CodeHasher::calculateCodeHash($code);
}






private static function getCache(string $key): self
{
if (!self::hasCache($key)) {
throw new \OutOfBoundsException(\sprintf('Unknown cache key: "%s".', $key));
}

return self::$cache[$key];
}






private static function hasCache(string $key): bool
{
return isset(self::$cache[$key]);
}





private static function setCache(string $key, self $value): void
{
self::$cache[$key] = $value;
}








private function changeCodeHash(string $codeHash): void
{
if (null !== $this->codeHash) {
self::clearCache($this->codeHash);
}

$this->codeHash = $codeHash;
self::setCache($this->codeHash, $this);
}






private function registerFoundToken($token): void
{


$tokenKind = $token instanceof Token
? ($token->isArray() ? $token->getId() : $token->getContent())
: (\is_array($token) ? $token[0] : $token);

$this->foundTokenKinds[$tokenKind] ??= 0;
++$this->foundTokenKinds[$tokenKind];
}






private function unregisterFoundToken($token): void
{


$tokenKind = $token instanceof Token
? ($token->isArray() ? $token->getId() : $token->getContent())
: (\is_array($token) ? $token[0] : $token);

if (1 === $this->foundTokenKinds[$tokenKind]) {
unset($this->foundTokenKinds[$tokenKind]);
} else {
--$this->foundTokenKinds[$tokenKind];
}
}






private function extractTokenKind($token)
{
return $token instanceof Token
? ($token->isArray() ? $token->getId() : $token->getContent())
: (\is_array($token) ? $token[0] : $token);
}






private function getTokenNotOfKind(int $index, int $direction, callable $filter): ?int
{
while (true) {
$index += $direction;
if (!$this->offsetExists($index)) {
return null;
}

if ($this->isEmptyAt($index) || $filter($index)) {
continue;
}

return $index;
}
}









private static function isKeyCaseSensitive($caseSensitive, int $key): bool
{
if (\is_array($caseSensitive)) {
return $caseSensitive[$key] ?? true;
}

return $caseSensitive;
}
}
