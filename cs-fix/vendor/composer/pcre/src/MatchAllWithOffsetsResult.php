<?php










namespace Composer\Pcre;

final class MatchAllWithOffsetsResult
{
/**
@readonly
@phpstan-var



*/
public $matches;

/**
@readonly

*/
public $count;

/**
@readonly

*/
public $matched;

/**
@phpstan-param


*/
public function __construct(int $count, array $matches)
{
$this->matches = $matches;
$this->matched = (bool) $count;
$this->count = $count;
}
}
