<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;




final class NamespaceAnalysis implements StartEndTokenAwareAnalysis
{



private string $fullName;




private string $shortName;




private int $startIndex;




private int $endIndex;




private int $scopeStartIndex;




private int $scopeEndIndex;

public function __construct(string $fullName, string $shortName, int $startIndex, int $endIndex, int $scopeStartIndex, int $scopeEndIndex)
{
$this->fullName = $fullName;
$this->shortName = $shortName;
$this->startIndex = $startIndex;
$this->endIndex = $endIndex;
$this->scopeStartIndex = $scopeStartIndex;
$this->scopeEndIndex = $scopeEndIndex;
}

public function getFullName(): string
{
return $this->fullName;
}

public function getShortName(): string
{
return $this->shortName;
}

public function getStartIndex(): int
{
return $this->startIndex;
}

public function getEndIndex(): int
{
return $this->endIndex;
}

public function getScopeStartIndex(): int
{
return $this->scopeStartIndex;
}

public function getScopeEndIndex(): int
{
return $this->scopeEndIndex;
}

public function isGlobalNamespace(): bool
{
return '' === $this->getFullName();
}
}
