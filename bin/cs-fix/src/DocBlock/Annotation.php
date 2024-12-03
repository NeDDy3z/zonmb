<?php

declare(strict_types=1);











namespace PhpCsFixer\DocBlock;

use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;







final class Annotation
{





private static array $tags = [
'method',
'param',
'property',
'property-read',
'property-write',
'return',
'throws',
'type',
'var',
];






private array $lines;






private $start;






private $end;






private $tag;






private $typesContent;






private $types;




private $namespace;




private array $namespaceUses;








public function __construct(array $lines, $namespace = null, array $namespaceUses = [])
{
$this->lines = array_values($lines);
$this->namespace = $namespace;
$this->namespaceUses = $namespaceUses;

$this->start = array_key_first($lines);
$this->end = array_key_last($lines);
}




public function __toString(): string
{
return $this->getContent();
}






public static function getTagsWithTypes(): array
{
return self::$tags;
}




public function getStart(): int
{
return $this->start;
}




public function getEnd(): int
{
return $this->end;
}




public function getTag(): Tag
{
if (null === $this->tag) {
$this->tag = new Tag($this->lines[0]);
}

return $this->tag;
}




public function getTypeExpression(): ?TypeExpression
{
$typesContent = $this->getTypesContent();

return null === $typesContent
? null
: new TypeExpression($typesContent, $this->namespace, $this->namespaceUses);
}




public function getVariableName(): ?string
{
$type = preg_quote($this->getTypesContent() ?? '', '/');
$regex = \sprintf(
'/@%s\s+(%s\s*)?(&\s*)?(\.{3}\s*)?(?<variable>\$%s)(?:.*|$)/',
$this->tag->getName(),
$type,
TypeExpression::REGEX_IDENTIFIER
);

if (Preg::match($regex, $this->lines[0]->getContent(), $matches)) {
return $matches['variable'];
}

return null;
}






public function getTypes(): array
{
if (null === $this->types) {
$typeExpression = $this->getTypeExpression();
$this->types = null === $typeExpression
? []
: $typeExpression->getTypes();
}

return $this->types;
}






public function setTypes(array $types): void
{
$pattern = '/'.preg_quote($this->getTypesContent(), '/').'/';

$this->lines[0]->setContent(Preg::replace($pattern, implode($this->getTypeExpression()->getTypesGlue(), $types), $this->lines[0]->getContent(), 1));

$this->clearCache();
}






public function getNormalizedTypes(): array
{
$normalized = array_map(static fn (string $type): string => strtolower($type), $this->getTypes());

sort($normalized);

return $normalized;
}




public function remove(): void
{
foreach ($this->lines as $line) {
if ($line->isTheStart() && $line->isTheEnd()) {

$line->remove();
} elseif ($line->isTheStart()) {

$content = Preg::replace('#(\s*/\*\*).*#', '$1', $line->getContent());

$line->setContent($content);
} elseif ($line->isTheEnd()) {

$content = Preg::replace('#(\s*)\S.*(\*/.*)#', '$1$2', $line->getContent());

$line->setContent($content);
} else {

$line->remove();
}
}

$this->clearCache();
}




public function getContent(): string
{
return implode('', $this->lines);
}

public function supportTypes(): bool
{
return \in_array($this->getTag()->getName(), self::$tags, true);
}






private function getTypesContent(): ?string
{
if (null === $this->typesContent) {
$name = $this->getTag()->getName();

if (!$this->supportTypes()) {
throw new \RuntimeException('This tag does not support types.');
}

$matchingResult = Preg::match(
'{^(?:\h*\*|/\*\*)[\h*]*@'.$name.'\h+'.TypeExpression::REGEX_TYPES.'(?:(?:[*\h\v]|\&?[\.\$]).*)?\r?$}is',
$this->lines[0]->getContent(),
$matches
);

$this->typesContent = $matchingResult
? $matches['types']
: null;
}

return $this->typesContent;
}

private function clearCache(): void
{
$this->types = null;
$this->typesContent = null;
}
}
