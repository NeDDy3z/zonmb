<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;




final class NoPhp4ConstructorFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Convert PHP4-style constructors to `__construct`.',
[
new CodeSample('<?php
class Foo
{
    public function Foo($bar)
    {
    }
}
'),
],
null,
'Risky when old style constructor being fixed is overridden or overrides parent one.'
);
}






public function getPriority(): int
{
return 75;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_CLASS);
}

public function isRisky(): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$tokensAnalyzer = new TokensAnalyzer($tokens);
$classes = array_keys($tokens->findGivenKind(T_CLASS));
$numClasses = \count($classes);

for ($i = 0; $i < $numClasses; ++$i) {
$index = $classes[$i];


if ($tokensAnalyzer->isAnonymousClass($index)) {
continue;
}


$nspIndex = $tokens->getPrevTokenOfKind($index, [[T_NAMESPACE, 'namespace']]);

if (null !== $nspIndex) {
$nspIndex = $tokens->getNextMeaningfulToken($nspIndex);


if (!$tokens[$nspIndex]->equals('{')) {

$nspIndex = $tokens->getNextTokenOfKind($nspIndex, [';', '{']);

if ($tokens[$nspIndex]->equals(';')) {

break;
}


$nspEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $nspIndex);

if ($index < $nspEnd) {

for ($j = $i + 1; $j < $numClasses; ++$j) {
if ($classes[$j] < $nspEnd) {
++$i;
}
}


continue;
}
}
}

$classNameIndex = $tokens->getNextMeaningfulToken($index);
$className = $tokens[$classNameIndex]->getContent();
$classStart = $tokens->getNextTokenOfKind($classNameIndex, ['{']);
$classEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $classStart);

$this->fixConstructor($tokens, $className, $classStart, $classEnd);
$this->fixParent($tokens, $classStart, $classEnd);
}
}









private function fixConstructor(Tokens $tokens, string $className, int $classStart, int $classEnd): void
{
$php4 = $this->findFunction($tokens, $className, $classStart, $classEnd);

if (null === $php4) {
return; 
}

if (isset($php4['modifiers'][T_ABSTRACT]) || isset($php4['modifiers'][T_STATIC])) {
return; 
}

$php5 = $this->findFunction($tokens, '__construct', $classStart, $classEnd);

if (null === $php5) {

$tokens[$php4['nameIndex']] = new Token([T_STRING, '__construct']);


$this->fixInfiniteRecursion($tokens, $php4['bodyIndex'], $php4['endIndex']);

return;
}


[$sequences, $case] = $this->getWrapperMethodSequence($tokens, '__construct', $php4['startIndex'], $php4['bodyIndex']);

foreach ($sequences as $seq) {
if (null !== $tokens->findSequence($seq, $php4['bodyIndex'] - 1, $php4['endIndex'], $case)) {

for ($i = $php4['startIndex']; $i <= $php4['endIndex']; ++$i) {
$tokens->clearAt($i);
}

return;
}
}


[$sequences, $case] = $this->getWrapperMethodSequence($tokens, $className, $php4['startIndex'], $php4['bodyIndex']);

foreach ($sequences as $seq) {
if (null !== $tokens->findSequence($seq, $php5['bodyIndex'] - 1, $php5['endIndex'], $case)) {

for ($i = $php5['startIndex']; $i <= $php5['endIndex']; ++$i) {
$tokens->clearAt($i);
}


$tokens[$php4['nameIndex']] = new Token([T_STRING, '__construct']);

return;
}
}
}








private function fixParent(Tokens $tokens, int $classStart, int $classEnd): void
{

foreach ($tokens->findGivenKind(T_EXTENDS) as $index => $token) {
$parentIndex = $tokens->getNextMeaningfulToken($index);
$parentClass = $tokens[$parentIndex]->getContent();


$parentSeq = $tokens->findSequence([
[T_STRING],
[T_DOUBLE_COLON],
[T_STRING, $parentClass],
'(',
], $classStart, $classEnd, [2 => false]);

if (null !== $parentSeq) {

$parentSeq = array_keys($parentSeq);


if ($tokens[$parentSeq[0]]->equalsAny([[T_STRING, 'parent'], [T_STRING, $parentClass]], false)) {

$tokens[$parentSeq[0]] = new Token([T_STRING, 'parent']);
$tokens[$parentSeq[2]] = new Token([T_STRING, '__construct']);
}
}

foreach (Token::getObjectOperatorKinds() as $objectOperatorKind) {

$parentSeq = $tokens->findSequence([
[T_VARIABLE, '$this'],
[$objectOperatorKind],
[T_STRING, $parentClass],
'(',
], $classStart, $classEnd, [2 => false]);

if (null !== $parentSeq) {

$parentSeq = array_keys($parentSeq);


$tokens[$parentSeq[0]] = new Token([
T_STRING,
'parent',
]);
$tokens[$parentSeq[1]] = new Token([
T_DOUBLE_COLON,
'::',
]);
$tokens[$parentSeq[2]] = new Token([T_STRING, '__construct']);
}
}
}
}









private function fixInfiniteRecursion(Tokens $tokens, int $start, int $end): void
{
foreach (Token::getObjectOperatorKinds() as $objectOperatorKind) {
$seq = [
[T_VARIABLE, '$this'],
[$objectOperatorKind],
[T_STRING, '__construct'],
];

while (true) {
$callSeq = $tokens->findSequence($seq, $start, $end, [2 => false]);

if (null === $callSeq) {
return;
}

$callSeq = array_keys($callSeq);

$tokens[$callSeq[0]] = new Token([T_STRING, 'parent']);
$tokens[$callSeq[1]] = new Token([T_DOUBLE_COLON, '::']);
}
}
}












private function getWrapperMethodSequence(Tokens $tokens, string $method, int $startIndex, int $bodyIndex): array
{
$sequences = [];

foreach (Token::getObjectOperatorKinds() as $objectOperatorKind) {

$seq = [
'{',
[T_VARIABLE, '$this'],
[$objectOperatorKind],
[T_STRING, $method],
'(',
];


$index = $startIndex;

while (true) {

$index = $tokens->getNextTokenOfKind($index, [[T_VARIABLE]]);

if (null === $index || $index >= $bodyIndex) {

break;
}


if (\count($seq) > 5) {
$seq[] = ',';
}


$seq[] = [T_VARIABLE, $tokens[$index]->getContent()];
}


$seq[] = ')';
$seq[] = ';';
$seq[] = '}';

$sequences[] = $seq;
}

return [$sequences, [3 => false]];
}
























private function findFunction(Tokens $tokens, string $name, int $startIndex, int $endIndex): ?array
{
$function = $tokens->findSequence([
[T_FUNCTION],
[T_STRING, $name],
'(',
], $startIndex, $endIndex, false);

if (null === $function) {
return null;
}


$function = array_keys($function);


$possibleModifiers = [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_ABSTRACT, T_FINAL];
$modifiers = [];

$prevBlock = $tokens->getPrevMeaningfulToken($function[0]);

while (null !== $prevBlock && $tokens[$prevBlock]->isGivenKind($possibleModifiers)) {
$modifiers[$tokens[$prevBlock]->getId()] = $prevBlock;
$prevBlock = $tokens->getPrevMeaningfulToken($prevBlock);
}

if (isset($modifiers[T_ABSTRACT])) {

$bodyStart = null;
$funcEnd = $tokens->getNextTokenOfKind($function[2], [';']);
} else {

$bodyStart = $tokens->getNextTokenOfKind($function[2], ['{']);
$funcEnd = null !== $bodyStart ? $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $bodyStart) : null;
}

return [
'nameIndex' => $function[1],
'startIndex' => $prevBlock + 1,
'endIndex' => $funcEnd,
'bodyIndex' => $bodyStart,
'modifiers' => $modifiers,
];
}
}
