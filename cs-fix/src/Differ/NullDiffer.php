<?php

declare(strict_types=1);











namespace PhpCsFixer\Differ;




final class NullDiffer implements DifferInterface
{
public function diff(string $old, string $new, ?\SplFileInfo $file = null): string
{
return '';
}
}
