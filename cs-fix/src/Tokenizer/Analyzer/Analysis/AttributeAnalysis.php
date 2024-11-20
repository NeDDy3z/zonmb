<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;

/**
@phpstan-type


*/
final class AttributeAnalysis
{
private int $startIndex;
private int $endIndex;
private int $openingBracketIndex;
private int $closingBracketIndex;




private array $attributes;




public function __construct(int $startIndex, int $endIndex, int $openingBracketIndex, int $closingBracketIndex, array $attributes)
{
$this->startIndex = $startIndex;
$this->endIndex = $endIndex;
$this->openingBracketIndex = $openingBracketIndex;
$this->closingBracketIndex = $closingBracketIndex;
$this->attributes = $attributes;
}

public function getStartIndex(): int
{
return $this->startIndex;
}

public function getEndIndex(): int
{
return $this->endIndex;
}

public function getOpeningBracketIndex(): int
{
return $this->openingBracketIndex;
}

public function getClosingBracketIndex(): int
{
return $this->closingBracketIndex;
}




public function getAttributes(): array
{
return $this->attributes;
}
}
