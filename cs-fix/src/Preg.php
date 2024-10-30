<?php

declare(strict_types=1);











namespace PhpCsFixer;









final class Preg
{
/**
@param-out($flags is PREG_OFFSET_CAPTURE? array<array-key, array{string, 0|positive-int}|array{'', -1}>: ($flags is PREG_UNMATCHED_AS_NULL? array<array-key, string|null>: ($flags is int-mask<PREG_OFFSET_CAPTURE, PREG_UNMATCHED_AS_NULL>&768? array<array-key, array{string, 0|positive-int}|array{null, -1}>: array<array-key, string>))) $matches














*/
public static function match(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
{
$result = @preg_match(self::addUtf8Modifier($pattern), $subject, $matches, $flags, $offset);
if (false !== $result && PREG_NO_ERROR === preg_last_error()) {
return 1 === $result;
}

$result = @preg_match(self::removeUtf8Modifier($pattern), $subject, $matches, $flags, $offset);
if (false !== $result && PREG_NO_ERROR === preg_last_error()) {
return 1 === $result;
}

throw self::newPregException(preg_last_error(), preg_last_error_msg(), __METHOD__, $pattern);
}

/**
@param-out($flags is PREG_PATTERN_ORDER? array<list<string>>: ($flags is PREG_SET_ORDER? list<array<string>>: ($flags is int-mask<PREG_PATTERN_ORDER, PREG_OFFSET_CAPTURE>&(256|257)? array<list<array{string, int}>>: ($flags is int-mask<PREG_SET_ORDER, PREG_OFFSET_CAPTURE>&258? list<array<array{string, int}>>: ($flags is int-mask<PREG_PATTERN_ORDER, PREG_UNMATCHED_AS_NULL>&(512|513)? array<list<?string>>: ($flags is int-mask<PREG_SET_ORDER, PREG_UNMATCHED_AS_NULL>&514? list<array<?string>>: ($flags is int-mask<PREG_SET_ORDER, PREG_OFFSET_CAPTURE, PREG_UNMATCHED_AS_NULL>&770? list<array<array{?string, int}>>: ($flags is 0 ? array<list<string>> : array<mixed>)))))))) $matches


























*/
public static function matchAll(string $pattern, string $subject, ?array &$matches = null, int $flags = PREG_PATTERN_ORDER, int $offset = 0): int
{
$result = @preg_match_all(self::addUtf8Modifier($pattern), $subject, $matches, $flags, $offset);
if (false !== $result && PREG_NO_ERROR === preg_last_error()) {
return $result;
}

$result = @preg_match_all(self::removeUtf8Modifier($pattern), $subject, $matches, $flags, $offset);
if (false !== $result && PREG_NO_ERROR === preg_last_error()) {
return $result;
}

throw self::newPregException(preg_last_error(), preg_last_error_msg(), __METHOD__, $pattern);
}

/**
@param-out




*/
public static function replace(string $pattern, string $replacement, $subject, int $limit = -1, ?int &$count = null): string
{
$result = @preg_replace(self::addUtf8Modifier($pattern), $replacement, $subject, $limit, $count);
if (null !== $result && PREG_NO_ERROR === preg_last_error()) {
return $result;
}

$result = @preg_replace(self::removeUtf8Modifier($pattern), $replacement, $subject, $limit, $count);
if (null !== $result && PREG_NO_ERROR === preg_last_error()) {
return $result;
}

throw self::newPregException(preg_last_error(), preg_last_error_msg(), __METHOD__, $pattern);
}

/**
@param-out


*/
public static function replaceCallback(string $pattern, callable $callback, string $subject, int $limit = -1, ?int &$count = null): string
{
$result = @preg_replace_callback(self::addUtf8Modifier($pattern), $callback, $subject, $limit, $count);
if (null !== $result && PREG_NO_ERROR === preg_last_error()) {
return $result;
}

$result = @preg_replace_callback(self::removeUtf8Modifier($pattern), $callback, $subject, $limit, $count);
if (null !== $result && PREG_NO_ERROR === preg_last_error()) {
return $result;
}

throw self::newPregException(preg_last_error(), preg_last_error_msg(), __METHOD__, $pattern);
}






public static function split(string $pattern, string $subject, int $limit = -1, int $flags = 0): array
{
$result = @preg_split(self::addUtf8Modifier($pattern), $subject, $limit, $flags);
if (false !== $result && PREG_NO_ERROR === preg_last_error()) {
return $result;
}

$result = @preg_split(self::removeUtf8Modifier($pattern), $subject, $limit, $flags);
if (false !== $result && PREG_NO_ERROR === preg_last_error()) {
return $result;
}

throw self::newPregException(preg_last_error(), preg_last_error_msg(), __METHOD__, $pattern);
}

private static function addUtf8Modifier(string $pattern): string
{
return $pattern.'u';
}

private static function removeUtf8Modifier(string $pattern): string
{
if ('' === $pattern) {
return '';
}

$delimiter = $pattern[0];

$endDelimiterPosition = strrpos($pattern, $delimiter);

return substr($pattern, 0, $endDelimiterPosition).str_replace('u', '', substr($pattern, $endDelimiterPosition));
}




private static function newPregException(int $error, string $errorMsg, string $method, string $pattern): PregException
{
$result = null;
$errorMessage = null;

try {
$result = ExecutorWithoutErrorHandler::execute(static fn () => preg_match($pattern, ''));
} catch (ExecutorWithoutErrorHandlerException $e) {
$result = false;
$errorMessage = $e->getMessage();
}

if (false !== $result) {
return new PregException(\sprintf('Unknown error occurred when calling %s: %s.', $method, $errorMsg), $error);
}

$code = preg_last_error();

$message = \sprintf(
'(code: %d) %s',
$code,
preg_replace('~preg_[a-z_]+[()]{2}: ~', '', $errorMessage)
);

return new PregException(
\sprintf('%s(): Invalid PCRE pattern "%s": %s (version: %s)', $method, $pattern, $message, PCRE_VERSION),
$code
);
}
}
