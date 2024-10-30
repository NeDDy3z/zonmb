<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;




final class EnumAnalysis extends AbstractControlCaseStructuresAnalysis
{



private array $cases;




public function __construct(int $index, int $open, int $close, array $cases)
{
parent::__construct($index, $open, $close);

$this->cases = $cases;
}




public function getCases(): array
{
return $this->cases;
}
}
