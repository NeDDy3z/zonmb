<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;




final class MatchAnalysis extends AbstractControlCaseStructuresAnalysis
{
private ?DefaultAnalysis $defaultAnalysis;

public function __construct(int $index, int $open, int $close, ?DefaultAnalysis $defaultAnalysis)
{
parent::__construct($index, $open, $close);

$this->defaultAnalysis = $defaultAnalysis;
}

public function getDefaultAnalysis(): ?DefaultAnalysis
{
return $this->defaultAnalysis;
}
}
