<?php










namespace Symfony\Component\Finder\Iterator;

use Symfony\Component\Finder\Glob;

/**
@extends




*/
class FilenameFilterIterator extends MultiplePcreFilterIterator
{





#[\ReturnTypeWillChange]
public function accept()
{
return $this->isAccepted($this->current()->getFilename());
}











protected function toRegex(string $str)
{
return $this->isRegex($str) ? $str : Glob::toRegex($str);
}
}
