<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;




final class DefaultAnalysis
{
private int $index;

private int $colonIndex;

public function __construct(int $index, int $colonIndex)
{
$this->index = $index;
$this->colonIndex = $colonIndex;
}

public function getIndex(): int
{
return $this->index;
}

public function getColonIndex(): int
{
return $this->colonIndex;
}
}
