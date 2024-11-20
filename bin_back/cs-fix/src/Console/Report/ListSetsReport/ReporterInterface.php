<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Report\ListSetsReport;






interface ReporterInterface
{
public function getFormat(): string;




public function generate(ReportSummary $reportSummary): string;
}
