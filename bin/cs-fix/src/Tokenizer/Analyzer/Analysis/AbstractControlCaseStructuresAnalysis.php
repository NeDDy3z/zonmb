<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;




abstract class AbstractControlCaseStructuresAnalysis
{
private int $index;

private int $open;

private int $close;

public function __construct(int $index, int $open, int $close)
{
$this->index = $index;
$this->open = $open;
$this->close = $close;
}

public function getIndex(): int
{
return $this->index;
}

public function getOpenIndex(): int
{
return $this->open;
}

public function getCloseIndex(): int
{
return $this->close;
}
}
