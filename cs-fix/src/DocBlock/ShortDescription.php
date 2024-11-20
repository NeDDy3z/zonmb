<?php

declare(strict_types=1);











namespace PhpCsFixer\DocBlock;






final class ShortDescription
{



private DocBlock $doc;

public function __construct(DocBlock $doc)
{
$this->doc = $doc;
}





public function getEnd(): ?int
{
$reachedContent = false;

foreach ($this->doc->getLines() as $index => $line) {


if ($reachedContent && ($line->containsATag() || !$line->containsUsefulContent())) {
return $index - 1;
}


if ($line->containsATag()) {
return null;
}



if ($line->containsUsefulContent()) {
$reachedContent = true;
}
}

return null;
}
}
