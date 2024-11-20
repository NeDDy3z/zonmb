<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\DocBlock\ShortDescription;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;





final class PhpdocTrimConsecutiveBlankLineSeparationFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Removes extra blank lines after summary and after description in PHPDoc.',
[
new CodeSample(
'<?php
/**
 * Summary.
 *
 *
 * Description that contain 4 lines,
 *
 *
 * while 2 of them are blank!
 *
 *
 * @param string $foo
 *
 *
 * @dataProvider provideFixCases
 */
function fnc($foo) {}
'
),
]
);
}







public function getPriority(): int
{
return -41;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_DOC_COMMENT)) {
continue;
}

$doc = new DocBlock($token->getContent());
$summaryEnd = (new ShortDescription($doc))->getEnd();

if (null !== $summaryEnd) {
$this->fixSummary($doc, $summaryEnd);
$this->fixDescription($doc, $summaryEnd);
}

$this->fixAllTheRest($doc);

$tokens[$index] = new Token([T_DOC_COMMENT, $doc->getContent()]);
}
}

private function fixSummary(DocBlock $doc, int $summaryEnd): void
{
$nonBlankLineAfterSummary = $this->findNonBlankLine($doc, $summaryEnd);

$this->removeExtraBlankLinesBetween($doc, $summaryEnd, $nonBlankLineAfterSummary);
}

private function fixDescription(DocBlock $doc, int $summaryEnd): void
{
$annotationStart = $this->findFirstAnnotationOrEnd($doc);


$descriptionEnd = $this->reverseFindLastUsefulContent($doc, $annotationStart);

if (null === $descriptionEnd || $summaryEnd === $descriptionEnd) {
return; 
}

if ($annotationStart === \count($doc->getLines()) - 1) {
return; 
}

$this->removeExtraBlankLinesBetween($doc, $descriptionEnd, $annotationStart);
}

private function fixAllTheRest(DocBlock $doc): void
{
$annotationStart = $this->findFirstAnnotationOrEnd($doc);
$lastLine = $this->reverseFindLastUsefulContent($doc, \count($doc->getLines()) - 1);

if (null !== $lastLine && $annotationStart !== $lastLine) {
$this->removeExtraBlankLinesBetween($doc, $annotationStart, $lastLine);
}
}

private function removeExtraBlankLinesBetween(DocBlock $doc, int $from, int $to): void
{
for ($index = $from + 1; $index < $to; ++$index) {
$line = $doc->getLine($index);
$next = $doc->getLine($index + 1);
$this->removeExtraBlankLine($line, $next);
}
}

private function removeExtraBlankLine(Line $current, Line $next): void
{
if (!$current->isTheEnd() && !$current->containsUsefulContent()
&& !$next->isTheEnd() && !$next->containsUsefulContent()) {
$current->remove();
}
}

private function findNonBlankLine(DocBlock $doc, int $after): ?int
{
foreach ($doc->getLines() as $index => $line) {
if ($index <= $after) {
continue;
}

if ($line->containsATag() || $line->containsUsefulContent() || $line->isTheEnd()) {
return $index;
}
}

return null;
}

private function findFirstAnnotationOrEnd(DocBlock $doc): int
{
foreach ($doc->getLines() as $index => $line) {
if ($line->containsATag()) {
return $index;
}
}

if (!isset($index)) {
throw new \LogicException('PHPDoc has empty lines collection.');
}

return $index; 
}

private function reverseFindLastUsefulContent(DocBlock $doc, int $from): ?int
{
for ($index = $from - 1; $index >= 0; --$index) {
if ($doc->getLine($index)->containsUsefulContent()) {
return $index;
}
}

return null;
}
}
