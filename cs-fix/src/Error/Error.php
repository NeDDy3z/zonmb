<?php

declare(strict_types=1);











namespace PhpCsFixer\Error;








final class Error implements \JsonSerializable
{



public const TYPE_INVALID = 1;




public const TYPE_EXCEPTION = 2;




public const TYPE_LINT = 3;


private int $type;

private string $filePath;

private ?\Throwable $source;




private array $appliedFixers;

private ?string $diff;





public function __construct(int $type, string $filePath, ?\Throwable $source = null, array $appliedFixers = [], ?string $diff = null)
{
$this->type = $type;
$this->filePath = $filePath;
$this->source = $source;
$this->appliedFixers = $appliedFixers;
$this->diff = $diff;
}

public function getFilePath(): string
{
return $this->filePath;
}

public function getSource(): ?\Throwable
{
return $this->source;
}

public function getType(): int
{
return $this->type;
}




public function getAppliedFixers(): array
{
return $this->appliedFixers;
}

public function getDiff(): ?string
{
return $this->diff;
}










public function jsonSerialize(): array
{
return [
'type' => $this->type,
'filePath' => $this->filePath,
'source' => null !== $this->source
? [
'class' => \get_class($this->source),
'message' => $this->source->getMessage(),
'code' => $this->source->getCode(),
'file' => $this->source->getFile(),
'line' => $this->source->getLine(),
]
: null,
'appliedFixers' => $this->appliedFixers,
'diff' => $this->diff,
];
}
}
