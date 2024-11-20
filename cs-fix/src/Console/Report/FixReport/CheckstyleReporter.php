<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Report\FixReport;

use PhpCsFixer\Console\Application;
use Symfony\Component\Console\Formatter\OutputFormatter;






final class CheckstyleReporter implements ReporterInterface
{
public function getFormat(): string
{
return 'checkstyle';
}

public function generate(ReportSummary $reportSummary): string
{
if (!\extension_loaded('dom')) {
throw new \RuntimeException('Cannot generate report! `ext-dom` is not available!');
}

$dom = new \DOMDocument('1.0', 'UTF-8');


$checkstyles = $dom->appendChild($dom->createElement('checkstyle'));
$checkstyles->setAttribute('version', Application::getAbout());

foreach ($reportSummary->getChanged() as $filePath => $fixResult) {

$file = $checkstyles->appendChild($dom->createElement('file'));
$file->setAttribute('name', $filePath);

foreach ($fixResult['appliedFixers'] as $appliedFixer) {
$error = $this->createError($dom, $appliedFixer);
$file->appendChild($error);
}
}

$dom->formatOutput = true;

return $reportSummary->isDecoratedOutput() ? OutputFormatter::escape($dom->saveXML()) : $dom->saveXML();
}

private function createError(\DOMDocument $dom, string $appliedFixer): \DOMElement
{
$error = $dom->createElement('error');
$error->setAttribute('severity', 'warning');
$error->setAttribute('source', 'PHP-CS-Fixer.'.$appliedFixer);
$error->setAttribute('message', 'Found violation(s) of type: '.$appliedFixer);

return $error;
}
}
