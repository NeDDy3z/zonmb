<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;

interface StartEndTokenAwareAnalysis
{



public function getStartIndex(): int;




public function getEndIndex(): int;
}
