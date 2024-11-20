<?php

declare(strict_types=1);











namespace PhpCsFixer\Differ;

use PhpCsFixer\Preg;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;

final class UnifiedDiffer implements DifferInterface
{
public function diff(string $old, string $new, ?\SplFileInfo $file = null): string
{
if (null === $file) {
$options = [
'fromFile' => 'Original',
'toFile' => 'New',
];
} else {
$filePath = $file->getRealPath();

if (Preg::match('/\s/', $filePath)) {
$filePath = '"'.$filePath.'"';
}

$options = [
'fromFile' => $filePath,
'toFile' => $filePath,
];
}

$differ = new Differ(new StrictUnifiedDiffOutputBuilder($options));

return $differ->diff($old, $new);
}
}
