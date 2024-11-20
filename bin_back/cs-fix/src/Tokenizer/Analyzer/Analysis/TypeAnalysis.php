<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;




final class TypeAnalysis implements StartEndTokenAwareAnalysis
{










private static array $reservedTypes = [
'array',
'bool',
'callable',
'false',
'float',
'int',
'iterable',
'list',
'mixed',
'never',
'null',
'object',
'parent',
'resource',
'self',
'static',
'string',
'true',
'void',
];

private string $name;

private int $startIndex;

private int $endIndex;

private bool $nullable = false;




public function __construct(string $name, ?int $startIndex = null, ?int $endIndex = null)
{
$this->name = $name;

if (str_starts_with($name, '?')) {
$this->name = substr($name, 1);
$this->nullable = true;
} elseif (\PHP_VERSION_ID >= 8_00_00) {
$this->nullable = \in_array('null', array_map('trim', explode('|', strtolower($name))), true);
}

if (null !== $startIndex) {
$this->startIndex = $startIndex;
$this->endIndex = $endIndex;
}
}

public function getName(): string
{
return $this->name;
}

public function getStartIndex(): int
{
return $this->startIndex;
}

public function getEndIndex(): int
{
return $this->endIndex;
}

public function isReservedType(): bool
{
return \in_array(strtolower($this->name), self::$reservedTypes, true);
}

public function isNullable(): bool
{
return $this->nullable;
}
}
