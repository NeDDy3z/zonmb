<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;




final class ArgumentAnalysis
{



private ?string $name;




private ?int $nameIndex;




private ?string $default;




private ?TypeAnalysis $typeAnalysis;

public function __construct(?string $name, ?int $nameIndex, ?string $default, ?TypeAnalysis $typeAnalysis = null)
{
$this->name = $name;
$this->nameIndex = $nameIndex;
$this->default = $default ?? null;
$this->typeAnalysis = $typeAnalysis ?? null;
}

public function getDefault(): ?string
{
return $this->default;
}

public function hasDefault(): bool
{
return null !== $this->default;
}

public function getName(): ?string
{
return $this->name;
}

public function getNameIndex(): ?int
{
return $this->nameIndex;
}

public function getTypeAnalysis(): ?TypeAnalysis
{
return $this->typeAnalysis;
}

public function hasTypeAnalysis(): bool
{
return null !== $this->typeAnalysis;
}
}
