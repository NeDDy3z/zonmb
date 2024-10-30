<?php

declare(strict_types=1);











namespace PhpCsFixer\Differ;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;






final class FullDiffer implements DifferInterface
{
private Differ $differ;

public function __construct()
{
$this->differ = new Differ(new StrictUnifiedDiffOutputBuilder([
'collapseRanges' => false,
'commonLineThreshold' => 100,
'contextLines' => 100,
'fromFile' => 'Original',
'toFile' => 'New',
]));
}

public function diff(string $old, string $new, ?\SplFileInfo $file = null): string
{
return $this->differ->diff($old, $new);
}
}
