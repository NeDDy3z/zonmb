<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Report\ListSetsReport;

use PhpCsFixer\RuleSet\RuleSetDescriptionInterface;






final class TextReporter implements ReporterInterface
{
public function getFormat(): string
{
return 'txt';
}

public function generate(ReportSummary $reportSummary): string
{
$sets = $reportSummary->getSets();

usort($sets, static fn (RuleSetDescriptionInterface $a, RuleSetDescriptionInterface $b): int => $a->getName() <=> $b->getName());

$output = '';

foreach ($sets as $i => $set) {
$output .= \sprintf('%2d) %s', $i + 1, $set->getName()).PHP_EOL.'      '.$set->getDescription().PHP_EOL;

if ($set->isRisky()) {
$output .= '      Set contains risky rules.'.PHP_EOL;
}
}

return $output;
}
}
