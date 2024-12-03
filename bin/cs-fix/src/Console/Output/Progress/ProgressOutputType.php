<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Output\Progress;




final class ProgressOutputType
{
public const NONE = 'none';
public const DOTS = 'dots';
public const BAR = 'bar';




public static function all(): array
{
return [
self::BAR,
self::DOTS,
self::NONE,
];
}
}
