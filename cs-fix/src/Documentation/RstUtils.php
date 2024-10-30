<?php

declare(strict_types=1);











namespace PhpCsFixer\Documentation;

use PhpCsFixer\Preg;




final class RstUtils
{
private function __construct()
{

}

public static function toRst(string $string, int $indent = 0): string
{
$string = wordwrap(self::ensureProperInlineCode($string), 80 - $indent);

return 0 === $indent ? $string : self::indent($string, $indent);
}

public static function ensureProperInlineCode(string $string): string
{
return Preg::replace('/(?<!`)(`[^`]+`)(?!`)/', '`$1`', $string);
}

public static function indent(string $string, int $indent): string
{
return Preg::replace('/(\n)(?!\n|$)/', '$1'.str_repeat(' ', $indent), $string);
}
}
