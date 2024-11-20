<?php

declare(strict_types=1);











namespace PhpCsFixer;






final class WordMatcher
{



private array $candidates;




public function __construct(array $candidates)
{
$this->candidates = $candidates;
}

public function match(string $needle): ?string
{
$word = null;
$distance = ceil(\strlen($needle) * 0.35);

foreach ($this->candidates as $candidate) {
$candidateDistance = levenshtein($needle, $candidate);

if ($candidateDistance < $distance) {
$word = $candidate;
$distance = $candidateDistance;
}
}

return $word;
}
}
