<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerConfiguration;




final class FixerOptionSorter
{





public function sort(iterable $options): array
{
if (!\is_array($options)) {
$options = iterator_to_array($options, false);
}

usort($options, static fn (FixerOptionInterface $a, FixerOptionInterface $b): int => $a->getName() <=> $b->getName());

return $options;
}
}
