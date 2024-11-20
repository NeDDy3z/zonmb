<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Report\FixReport;

use PhpCsFixer\Console\Application;
use SebastianBergmann\Diff\Chunk;
use SebastianBergmann\Diff\Diff;
use SebastianBergmann\Diff\Parser;
use Symfony\Component\Console\Formatter\OutputFormatter;










final class GitlabReporter implements ReporterInterface
{
private Parser $diffParser;

public function __construct()
{
$this->diffParser = new Parser();
}

public function getFormat(): string
{
return 'gitlab';
}




public function generate(ReportSummary $reportSummary): string
{
$about = Application::getAbout();

$report = [];
foreach ($reportSummary->getChanged() as $fileName => $change) {
foreach ($change['appliedFixers'] as $fixerName) {
$report[] = [
'check_name' => 'PHP-CS-Fixer.'.$fixerName,
'description' => 'PHP-CS-Fixer.'.$fixerName.' by '.$about,
'categories' => ['Style'],
'fingerprint' => md5($fileName.$fixerName),
'severity' => 'minor',
'location' => [
'path' => $fileName,
'lines' => self::getLines($this->diffParser->parse($change['diff'])),
],
];
}
}

$jsonString = json_encode($report, JSON_THROW_ON_ERROR);

return $reportSummary->isDecoratedOutput() ? OutputFormatter::escape($jsonString) : $jsonString;
}






private static function getLines(array $diffs): array
{
if (isset($diffs[0])) {
$firstDiff = $diffs[0];

$firstChunk = \Closure::bind(static fn (Diff $diff) => array_shift($diff->chunks), null, $firstDiff)($firstDiff);

if ($firstChunk instanceof Chunk) {
return \Closure::bind(static fn (Chunk $chunk): array => ['begin' => $chunk->start, 'end' => $chunk->startRange], null, $firstChunk)($firstChunk);
}
}

return ['begin' => 0, 'end' => 0];
}
}
