<?php

declare(strict_types=1);











namespace PhpCsFixer\DocBlock;

use PhpCsFixer\Preg;







final class Tag
{



public const PSR_STANDARD_TAGS = [
'api', 'author', 'category', 'copyright', 'deprecated', 'example',
'global', 'internal', 'license', 'link', 'method', 'package', 'param',
'property', 'property-read', 'property-write', 'return', 'see',
'since', 'subpackage', 'throws', 'todo', 'uses', 'var', 'version',
];




private Line $line;




private ?string $name = null;




public function __construct(Line $line)
{
$this->line = $line;
}






public function getName(): string
{
if (null === $this->name) {
Preg::matchAll('/@[a-zA-Z0-9_-]+(?=\s|$)/', $this->line->getContent(), $matches);

if (isset($matches[0][0])) {
$this->name = ltrim($matches[0][0], '@');
} else {
$this->name = 'other';
}
}

return $this->name;
}






public function setName(string $name): void
{
$current = $this->getName();

if ('other' === $current) {
throw new \RuntimeException('Cannot set name on unknown tag.');
}

$this->line->setContent(Preg::replace("/@{$current}/", "@{$name}", $this->line->getContent(), 1));

$this->name = $name;
}






public function valid(): bool
{
return \in_array($this->getName(), self::PSR_STANDARD_TAGS, true);
}
}
