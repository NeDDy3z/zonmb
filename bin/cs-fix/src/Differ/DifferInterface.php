<?php

declare(strict_types=1);











namespace PhpCsFixer\Differ;




interface DifferInterface
{



public function diff(string $old, string $new, ?\SplFileInfo $file = null): string;
}
