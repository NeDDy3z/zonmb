<?php










namespace Composer\Pcre;

final class ReplaceResult
{
/**
@readonly

*/
public $result;

/**
@readonly

*/
public $count;

/**
@readonly

*/
public $matched;




public function __construct(int $count, string $result)
{
$this->count = $count;
$this->matched = (bool) $count;
$this->result = $result;
}
}
