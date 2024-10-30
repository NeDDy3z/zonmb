<?php

declare(strict_types=1);











namespace PhpCsFixer\DocBlock;

use PhpCsFixer\Preg;






final class Line
{



private string $content;




public function __construct(string $content)
{
$this->content = $content;
}




public function __toString(): string
{
return $this->content;
}




public function getContent(): string
{
return $this->content;
}






public function containsUsefulContent(): bool
{
return Preg::match('/\*\s*\S+/', $this->content) && '' !== trim(str_replace(['/', '*'], ' ', $this->content));
}






public function containsATag(): bool
{
return Preg::match('/\*\s*@/', $this->content);
}




public function isTheStart(): bool
{
return str_contains($this->content, '/**');
}




public function isTheEnd(): bool
{
return str_contains($this->content, '*/');
}




public function setContent(string $content): void
{
$this->content = $content;
}








public function remove(): void
{
$this->content = '';
}








public function addBlank(): void
{
$matched = Preg::match('/^(\h*\*)[^\r\n]*(\r?\n)$/', $this->content, $matches);

if (!$matched) {
return;
}

$this->content .= $matches[1].$matches[2];
}
}
