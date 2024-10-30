<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer;

use PhpCsFixer\Utils;







final class Token
{



private string $content;




private ?int $id = null;




private bool $isArray;




private bool $changed = false;




public function __construct($token)
{
if (\is_array($token)) {
if (!\is_int($token[0])) {
throw new \InvalidArgumentException(\sprintf(
'Id must be an int, got "%s".',
get_debug_type($token[0])
));
}

if (!\is_string($token[1])) {
throw new \InvalidArgumentException(\sprintf(
'Content must be a string, got "%s".',
get_debug_type($token[1])
));
}

if ('' === $token[1]) {
throw new \InvalidArgumentException('Cannot set empty content for id-based Token.');
}

$this->isArray = true;
$this->id = $token[0];
$this->content = $token[1];
} elseif (\is_string($token)) {
$this->isArray = false;
$this->content = $token;
} else {
throw new \InvalidArgumentException(\sprintf('Cannot recognize input value as valid Token prototype, got "%s".', get_debug_type($token)));
}
}




public static function getCastTokenKinds(): array
{
static $castTokens = [T_ARRAY_CAST, T_BOOL_CAST, T_DOUBLE_CAST, T_INT_CAST, T_OBJECT_CAST, T_STRING_CAST, T_UNSET_CAST];

return $castTokens;
}






public static function getClassyTokenKinds(): array
{
static $classTokens;

if (null === $classTokens) {
$classTokens = [T_CLASS, T_TRAIT, T_INTERFACE];

if (\defined('T_ENUM')) { 
$classTokens[] = T_ENUM;
}
}

return $classTokens;
}






public static function getObjectOperatorKinds(): array
{
static $objectOperators = null;

if (null === $objectOperators) {
$objectOperators = [T_OBJECT_OPERATOR];
if (\defined('T_NULLSAFE_OBJECT_OPERATOR')) {
$objectOperators[] = T_NULLSAFE_OBJECT_OPERATOR;
}
}

return $objectOperators;
}









public function equals($other, bool $caseSensitive = true): bool
{
if (\defined('T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG')) { 
if ('&' === $other) {
return '&' === $this->content && (null === $this->id || $this->isGivenKind([T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG, T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG]));
}
if (null === $this->id && '&' === $this->content) {
return $other instanceof self && '&' === $other->content && (null === $other->id || $other->isGivenKind([T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG, T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG]));
}
}

if ($other instanceof self) {



if (!$other->isArray) {
$otherPrototype = $other->content;
} else {
$otherPrototype = [
$other->id,
$other->content,
];
}
} else {
$otherPrototype = $other;
}

if ($this->isArray !== \is_array($otherPrototype)) {
return false;
}

if (!$this->isArray) {
return $this->content === $otherPrototype;
}

if ($this->id !== $otherPrototype[0]) {
return false;
}

if (isset($otherPrototype[1])) {
if ($caseSensitive) {
if ($this->content !== $otherPrototype[1]) {
return false;
}
} elseif (0 !== strcasecmp($this->content, $otherPrototype[1])) {
return false;
}
}


unset($otherPrototype[0], $otherPrototype[1]);

return [] === $otherPrototype;
}







public function equalsAny(array $others, bool $caseSensitive = true): bool
{
foreach ($others as $other) {
if ($this->equals($other, $caseSensitive)) {
return true;
}
}

return false;
}











public static function isKeyCaseSensitive($caseSensitive, int $key): bool
{
Utils::triggerDeprecation(new \InvalidArgumentException(\sprintf(
'Method "%s" is deprecated and will be removed in the next major version.',
__METHOD__
)));

if (\is_array($caseSensitive)) {
return $caseSensitive[$key] ?? true;
}

return $caseSensitive;
}




public function getPrototype()
{
if (!$this->isArray) {
return $this->content;
}

return [
$this->id,
$this->content,
];
}








public function getContent(): string
{
return $this->content;
}






public function getId(): ?int
{
return $this->id;
}








public function getName(): ?string
{
if (null === $this->id) {
return null;
}

return self::getNameForId($this->id);
}








public static function getNameForId(int $id): ?string
{
if (CT::has($id)) {
return CT::getName($id);
}

$name = token_name($id);

return 'UNKNOWN' === $name ? null : $name;
}






public static function getKeywords(): array
{
static $keywords = null;

if (null === $keywords) {
$keywords = self::getTokenKindsForNames(['T_ABSTRACT', 'T_ARRAY', 'T_AS', 'T_BREAK', 'T_CALLABLE', 'T_CASE',
'T_CATCH', 'T_CLASS', 'T_CLONE', 'T_CONST', 'T_CONTINUE', 'T_DECLARE', 'T_DEFAULT', 'T_DO',
'T_ECHO', 'T_ELSE', 'T_ELSEIF', 'T_EMPTY', 'T_ENDDECLARE', 'T_ENDFOR', 'T_ENDFOREACH',
'T_ENDIF', 'T_ENDSWITCH', 'T_ENDWHILE', 'T_EVAL', 'T_EXIT', 'T_EXTENDS', 'T_FINAL',
'T_FINALLY', 'T_FN', 'T_FOR', 'T_FOREACH', 'T_FUNCTION', 'T_GLOBAL', 'T_GOTO', 'T_HALT_COMPILER',
'T_IF', 'T_IMPLEMENTS', 'T_INCLUDE', 'T_INCLUDE_ONCE', 'T_INSTANCEOF', 'T_INSTEADOF',
'T_INTERFACE', 'T_ISSET', 'T_LIST', 'T_LOGICAL_AND', 'T_LOGICAL_OR', 'T_LOGICAL_XOR',
'T_NAMESPACE', 'T_MATCH', 'T_NEW', 'T_PRINT', 'T_PRIVATE', 'T_PROTECTED', 'T_PUBLIC', 'T_REQUIRE',
'T_REQUIRE_ONCE', 'T_RETURN', 'T_STATIC', 'T_SWITCH', 'T_THROW', 'T_TRAIT', 'T_TRY',
'T_UNSET', 'T_USE', 'T_VAR', 'T_WHILE', 'T_YIELD', 'T_YIELD_FROM', 'T_READONLY', 'T_ENUM',
]) + [
CT::T_ARRAY_TYPEHINT => CT::T_ARRAY_TYPEHINT,
CT::T_CLASS_CONSTANT => CT::T_CLASS_CONSTANT,
CT::T_CONST_IMPORT => CT::T_CONST_IMPORT,
CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE => CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE,
CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED => CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED,
CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC => CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC,
CT::T_FUNCTION_IMPORT => CT::T_FUNCTION_IMPORT,
CT::T_NAMESPACE_OPERATOR => CT::T_NAMESPACE_OPERATOR,
CT::T_USE_LAMBDA => CT::T_USE_LAMBDA,
CT::T_USE_TRAIT => CT::T_USE_TRAIT,
];
}

return $keywords;
}








public static function getMagicConstants(): array
{
static $magicConstants = null;

if (null === $magicConstants) {
$magicConstants = self::getTokenKindsForNames(['T_CLASS_C', 'T_DIR', 'T_FILE', 'T_FUNC_C', 'T_LINE', 'T_METHOD_C', 'T_NS_C', 'T_TRAIT_C']);
}

return $magicConstants;
}






public function isArray(): bool
{
return $this->isArray;
}




public function isCast(): bool
{
return $this->isGivenKind(self::getCastTokenKinds());
}




public function isClassy(): bool
{
return $this->isGivenKind(self::getClassyTokenKinds());
}




public function isComment(): bool
{
static $commentTokens = [T_COMMENT, T_DOC_COMMENT];

return $this->isGivenKind($commentTokens);
}




public function isObjectOperator(): bool
{
return $this->isGivenKind(self::getObjectOperatorKinds());
}






public function isGivenKind($possibleKind): bool
{
return $this->isArray && (\is_array($possibleKind) ? \in_array($this->id, $possibleKind, true) : $this->id === $possibleKind);
}




public function isKeyword(): bool
{
$keywords = self::getKeywords();

return $this->isArray && isset($keywords[$this->id]);
}




public function isNativeConstant(): bool
{
static $nativeConstantStrings = ['true', 'false', 'null'];

return $this->isArray && \in_array(strtolower($this->content), $nativeConstantStrings, true);
}






public function isMagicConstant(): bool
{
$magicConstants = self::getMagicConstants();

return $this->isArray && isset($magicConstants[$this->id]);
}






public function isWhitespace(?string $whitespaces = " \t\n\r\0\x0B"): bool
{
if (null === $whitespaces) {
$whitespaces = " \t\n\r\0\x0B";
}

if ($this->isArray && !$this->isGivenKind(T_WHITESPACE)) {
return false;
}

return '' === trim($this->content, $whitespaces);
}










public function toArray(): array
{
return [
'id' => $this->id,
'name' => $this->getName(),
'content' => $this->content,
'isArray' => $this->isArray,
'changed' => $this->changed,
];
}

public function toJson(): string
{
$jsonResult = json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);

if (JSON_ERROR_NONE !== json_last_error()) {
$jsonResult = json_encode(
[
'errorDescription' => 'Cannot encode Tokens to JSON.',
'rawErrorMessage' => json_last_error_msg(),
],
JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK
);
}

return $jsonResult;
}






private static function getTokenKindsForNames(array $tokenNames): array
{
$keywords = [];
foreach ($tokenNames as $keywordName) {
if (\defined($keywordName)) {
$keyword = \constant($keywordName);
$keywords[$keyword] = $keyword;
}
}

return $keywords;
}
}
