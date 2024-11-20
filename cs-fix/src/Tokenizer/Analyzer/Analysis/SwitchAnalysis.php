<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;




final class SwitchAnalysis extends AbstractControlCaseStructuresAnalysis
{



private array $cases;

private ?DefaultAnalysis $defaultAnalysis;




public function __construct(int $index, int $open, int $close, array $cases, ?DefaultAnalysis $defaultAnalysis)
{
parent::__construct($index, $open, $close);

$this->cases = $cases;
$this->defaultAnalysis = $defaultAnalysis;
}




public function getCases(): array
{
return $this->cases;
}

public function getDefaultAnalysis(): ?DefaultAnalysis
{
return $this->defaultAnalysis;
}
}
