<?php










namespace Composer\Pcre;

final class MatchAllStrictGroupsResult
{
/**
@readonly



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





public function __construct(int $count, array $matches)
{
$this->matches = $matches;
$this->matched = (bool) $count;
$this->count = $count;
}
}
