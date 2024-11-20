<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Token;










final class Utils
{



private static array $deprecations = [];

private function __construct()
{

}




public static function camelCaseToUnderscore(string $string): string
{
return mb_strtolower(Preg::replace('/(?<!^)(?<!_)((?=[\p{Lu}][^\p{Lu}])|(?<![\p{Lu}])(?=[\p{Lu}]))/', '_', $string));
}






public static function calculateTrailingWhitespaceIndent(Token $token): string
{
if (!$token->isWhitespace()) {
throw new \InvalidArgumentException(\sprintf('The given token must be whitespace, got "%s".', $token->getName()));
}

$str = strrchr(
str_replace(["\r\n", "\r"], "\n", $token->getContent()),
"\n"
);

if (false === $str) {
return '';
}

return ltrim($str, "\n");
}

/**
@template
@template










*/
public static function stableSort(array $elements, callable $getComparedValue, callable $compareValues): array
{
array_walk($elements, static function (&$element, int $index) use ($getComparedValue): void {
$element = [$element, $index, $getComparedValue($element)];
});

usort($elements, static function ($a, $b) use ($compareValues): int {
$comparison = $compareValues($a[2], $b[2]);

if (0 !== $comparison) {
return $comparison;
}

return $a[1] <=> $b[1];
});

return array_map(static fn (array $item) => $item[0], $elements);
}








public static function sortFixers(array $fixers): array
{


return self::stableSort(
$fixers,
static fn (FixerInterface $fixer): int => $fixer->getPriority(),
static fn (int $a, int $b): int => $b <=> $a
);
}








public static function naturalLanguageJoin(array $names, string $wrapper = '"'): string
{
if (0 === \count($names)) {
throw new \InvalidArgumentException('Array of names cannot be empty.');
}

if (\strlen($wrapper) > 1) {
throw new \InvalidArgumentException('Wrapper should be a single-char string or empty.');
}

$names = array_map(static fn (string $name): string => \sprintf('%2$s%1$s%2$s', $name, $wrapper), $names);

$last = array_pop($names);

if (\count($names) > 0) {
return implode(', ', $names).' and '.$last;
}

return $last;
}








public static function naturalLanguageJoinWithBackticks(array $names): string
{
return self::naturalLanguageJoin($names, '`');
}

public static function isFutureModeEnabled(): bool
{
return filter_var(
getenv('PHP_CS_FIXER_FUTURE_MODE'),
FILTER_VALIDATE_BOOL
);
}

public static function triggerDeprecation(\Exception $futureException): void
{
if (self::isFutureModeEnabled()) {
throw new \RuntimeException(
'Your are using something deprecated, see previous exception. Aborting execution because `PHP_CS_FIXER_FUTURE_MODE` environment variable is set.',
0,
$futureException
);
}

$message = $futureException->getMessage();

self::$deprecations[$message] = true;
@trigger_error($message, E_USER_DEPRECATED);
}




public static function getTriggeredDeprecations(): array
{
$triggeredDeprecations = array_keys(self::$deprecations);
sort($triggeredDeprecations);

return $triggeredDeprecations;
}

public static function convertArrayTypeToList(string $type): string
{
$parts = explode('[]', $type);
$count = \count($parts) - 1;

return str_repeat('list<', $count).$parts[0].str_repeat('>', $count);
}




public static function toString($value): string
{
return \is_array($value)
? self::arrayToString($value)
: self::scalarToString($value);
}




private static function scalarToString($value): string
{
$str = var_export($value, true);

return Preg::replace('/\bNULL\b/', 'null', $str);
}




private static function arrayToString(array $value): string
{
if (0 === \count($value)) {
return '[]';
}

$isHash = !array_is_list($value);
$str = '[';

foreach ($value as $k => $v) {
if ($isHash) {
$str .= self::scalarToString($k).' => ';
}

$str .= \is_array($v)
? self::arrayToString($v).', '
: self::scalarToString($v).', ';
}

return substr($str, 0, -2).']';
}
}
