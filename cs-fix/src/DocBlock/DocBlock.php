<?php

declare(strict_types=1);











namespace PhpCsFixer\DocBlock;

use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;








final class DocBlock
{



private array $lines = [];




private ?array $annotations = null;

private ?NamespaceAnalysis $namespace;




private array $namespaceUses;




public function __construct(string $content, ?NamespaceAnalysis $namespace = null, array $namespaceUses = [])
{
foreach (Preg::split('/([^\n\r]+\R*)/', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $line) {
$this->lines[] = new Line($line);
}

$this->namespace = $namespace;
$this->namespaceUses = $namespaceUses;
}

public function __toString(): string
{
return $this->getContent();
}






public function getLines(): array
{
return $this->lines;
}




public function getLine(int $pos): ?Line
{
return $this->lines[$pos] ?? null;
}






public function getAnnotations(): array
{
if (null !== $this->annotations) {
return $this->annotations;
}

$this->annotations = [];
$total = \count($this->lines);

for ($index = 0; $index < $total; ++$index) {
if ($this->lines[$index]->containsATag()) {

$lines = \array_slice($this->lines, $index, $this->findAnnotationLength($index), true);
$annotation = new Annotation($lines, $this->namespace, $this->namespaceUses);



$index = $annotation->getEnd();

$this->annotations[] = $annotation;
}
}

return $this->annotations;
}

public function isMultiLine(): bool
{
return 1 !== \count($this->lines);
}




public function makeMultiLine(string $indent, string $lineEnd): void
{
if ($this->isMultiLine()) {
return;
}

$lineContent = $this->getSingleLineDocBlockEntry($this->lines[0]);

if ('' === $lineContent) {
$this->lines = [
new Line('/**'.$lineEnd),
new Line($indent.' *'.$lineEnd),
new Line($indent.' */'),
];

return;
}

$this->lines = [
new Line('/**'.$lineEnd),
new Line($indent.' * '.$lineContent.$lineEnd),
new Line($indent.' */'),
];
}

public function makeSingleLine(): void
{
if (!$this->isMultiLine()) {
return;
}

$usefulLines = array_filter(
$this->lines,
static fn (Line $line): bool => $line->containsUsefulContent()
);

if (1 < \count($usefulLines)) {
return;
}

$lineContent = '';
if (\count($usefulLines) > 0) {
$lineContent = $this->getSingleLineDocBlockEntry(array_shift($usefulLines));
}

$this->lines = [new Line('/** '.$lineContent.' */')];
}

public function getAnnotation(int $pos): ?Annotation
{
$annotations = $this->getAnnotations();

return $annotations[$pos] ?? null;
}








public function getAnnotationsOfType($types): array
{
$typesToSearchFor = (array) $types;

$annotations = [];

foreach ($this->getAnnotations() as $annotation) {
$tagName = $annotation->getTag()->getName();
if (\in_array($tagName, $typesToSearchFor, true)) {
$annotations[] = $annotation;
}
}

return $annotations;
}




public function getContent(): string
{
return implode('', $this->lines);
}

private function findAnnotationLength(int $start): int
{
$index = $start;

while ($line = $this->getLine(++$index)) {
if ($line->containsATag()) {

break;
}

if (!$line->containsUsefulContent()) {

$next = $this->getLine($index + 1);
if (null === $next || !$next->containsUsefulContent() || $next->containsATag()) {
break;
}

}
}

return $index - $start;
}

private function getSingleLineDocBlockEntry(Line $line): string
{
$lineString = $line->getContent();

if ('' === $lineString) {
return $lineString;
}

$lineString = str_replace('*/', '', $lineString);
$lineString = trim($lineString);

if (str_starts_with($lineString, '/**')) {
$lineString = substr($lineString, 3);
} elseif (str_starts_with($lineString, '*')) {
$lineString = substr($lineString, 1);
}

return trim($lineString);
}
}
