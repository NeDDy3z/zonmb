<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Report\ListSetsReport;

use PhpCsFixer\RuleSet\RuleSetDescriptionInterface;






final class ReportSummary
{



private array $sets;




public function __construct(array $sets)
{
$this->sets = $sets;
}




public function getSets(): array
{
return $this->sets;
}
}
