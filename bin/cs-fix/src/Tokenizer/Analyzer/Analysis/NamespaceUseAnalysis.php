<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;

/**
@phpstan-type





*/
final class NamespaceUseAnalysis implements StartEndTokenAwareAnalysis
{
public const TYPE_CLASS = 1; 
public const TYPE_FUNCTION = 2;
public const TYPE_CONSTANT = 3;






private string $fullName;




private string $shortName;




private bool $isInMulti;




private bool $isAliased;




private int $startIndex;




private int $endIndex;




private ?int $chunkStartIndex;




private ?int $chunkEndIndex;






private int $type;





public function __construct(
int $type,
string $fullName,
string $shortName,
bool $isAliased,
bool $isInMulti,
int $startIndex,
int $endIndex,
?int $chunkStartIndex = null,
?int $chunkEndIndex = null
) {
if (true === $isInMulti && (null === $chunkStartIndex || null === $chunkEndIndex)) {
throw new \LogicException('Chunk start and end index must be set when the import is part of a multi-use statement.');
}

$this->type = $type;
$this->fullName = $fullName;
$this->shortName = $shortName;
$this->isAliased = $isAliased;
$this->isInMulti = $isInMulti;
$this->startIndex = $startIndex;
$this->endIndex = $endIndex;
$this->chunkStartIndex = $chunkStartIndex;
$this->chunkEndIndex = $chunkEndIndex;
}




public function getFullName(): string
{
return $this->fullName;
}

public function getShortName(): string
{
return $this->shortName;
}

public function isAliased(): bool
{
return $this->isAliased;
}

public function isInMulti(): bool
{
return $this->isInMulti;
}

public function getStartIndex(): int
{
return $this->startIndex;
}

public function getEndIndex(): int
{
return $this->endIndex;
}

public function getChunkStartIndex(): ?int
{
return $this->chunkStartIndex;
}

public function getChunkEndIndex(): ?int
{
return $this->chunkEndIndex;
}




public function getType(): int
{
return $this->type;
}




public function getHumanFriendlyType(): string
{
return [
self::TYPE_CLASS => 'class',
self::TYPE_FUNCTION => 'function',
self::TYPE_CONSTANT => 'constant',
][$this->type];
}

public function isClass(): bool
{
return self::TYPE_CLASS === $this->type;
}

public function isFunction(): bool
{
return self::TYPE_FUNCTION === $this->type;
}

public function isConstant(): bool
{
return self::TYPE_CONSTANT === $this->type;
}
}
