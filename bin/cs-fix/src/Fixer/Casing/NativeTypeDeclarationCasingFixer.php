<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Casing;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\TypeAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

final class NativeTypeDeclarationCasingFixer extends AbstractFixer
{
























private const CLASS_CONST_SUPPORTED_HINTS = [
'array' => true,
'bool' => true,
'float' => true,
'int' => true,
'iterable' => true,
'mixed' => true,
'null' => true,
'object' => true,
'parent' => true,
'self' => true,
'string' => true,
'static' => true,
];

private const CLASS_PROPERTY_SUPPORTED_HINTS = [
'array' => true,
'bool' => true,
'float' => true,
'int' => true,
'iterable' => true,
'mixed' => true,
'null' => true,
'object' => true,
'parent' => true,
'self' => true,
'static' => true,
'string' => true,
];

private const TYPE_SEPARATION_TYPES = [
CT::T_TYPE_ALTERNATION,
CT::T_TYPE_INTERSECTION,
CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN,
CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE,
];

























private array $functionTypeHints;

private FunctionsAnalyzer $functionsAnalyzer;




private array $beforePropertyTypeTokens;

public function __construct()
{
parent::__construct();

$this->beforePropertyTypeTokens = ['{', ';', [T_PRIVATE], [T_PROTECTED], [T_PUBLIC], [T_VAR]];

$this->functionTypeHints = [
'array' => true,
'bool' => true,
'callable' => true,
'float' => true,
'int' => true,
'iterable' => true,
'object' => true,
'self' => true,
'string' => true,
'void' => true,
];

if (\PHP_VERSION_ID >= 8_00_00) {
$this->functionTypeHints['false'] = true;
$this->functionTypeHints['mixed'] = true;
$this->functionTypeHints['null'] = true;
$this->functionTypeHints['static'] = true;
}

if (\PHP_VERSION_ID >= 8_01_00) {
$this->functionTypeHints['never'] = true;

$this->beforePropertyTypeTokens[] = [T_READONLY];
}

if (\PHP_VERSION_ID >= 8_02_00) {
$this->functionTypeHints['true'] = true;
}

$this->functionsAnalyzer = new FunctionsAnalyzer();
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Native type declarations should be used in the correct case.',
[
new CodeSample(
"<?php\nclass Bar {\n    public function Foo(CALLABLE \$bar): INT\n    {\n        return 1;\n    }\n}\n"
),
new VersionSpecificCodeSample(
"<?php\nclass Foo\n{\n    const INT BAR = 1;\n}\n",
new VersionSpecification(8_03_00),
),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
$classyFound = $tokens->isAnyTokenKindsFound(Token::getClassyTokenKinds());

return
$tokens->isAnyTokenKindsFound([T_FUNCTION, T_FN])
|| ($classyFound && $tokens->isTokenKindFound(T_STRING))
|| (
\PHP_VERSION_ID >= 8_03_00
&& $tokens->isTokenKindFound(T_CONST)
&& $classyFound
);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$this->fixFunctions($tokens);
$this->fixClassConstantsAndProperties($tokens);
}

private function fixFunctions(Tokens $tokens): void
{
for ($index = $tokens->count() - 1; $index >= 0; --$index) {
if ($tokens[$index]->isGivenKind([T_FUNCTION, T_FN])) {
$this->fixFunctionReturnType($tokens, $index);
$this->fixFunctionArgumentTypes($tokens, $index);
}
}
}

private function fixFunctionArgumentTypes(Tokens $tokens, int $index): void
{
foreach ($this->functionsAnalyzer->getFunctionArguments($tokens, $index) as $argument) {
$this->fixArgumentType($tokens, $argument->getTypeAnalysis());
}
}

private function fixFunctionReturnType(Tokens $tokens, int $index): void
{
$this->fixArgumentType($tokens, $this->functionsAnalyzer->getFunctionReturnType($tokens, $index));
}

private function fixArgumentType(Tokens $tokens, ?TypeAnalysis $type = null): void
{
if (null === $type) {
return;
}

for ($index = $type->getStartIndex(); $index <= $type->getEndIndex(); ++$index) {
if ($tokens[$tokens->getNextMeaningfulToken($index)]->isGivenKind(T_NS_SEPARATOR)) {
continue;
}

$this->fixCasing($this->functionTypeHints, $tokens, $index);
}
}

private function fixClassConstantsAndProperties(Tokens $tokens): void
{
$analyzer = new TokensAnalyzer($tokens);
$elements = array_reverse($analyzer->getClassyElements(), true);

foreach ($elements as $index => $element) {
if ('const' === $element['type']) {
if (\PHP_VERSION_ID >= 8_03_00 && !$this->isConstWithoutType($tokens, $index)) {
foreach ($this->getNativeTypeHintCandidatesForConstant($tokens, $index) as $nativeTypeHintIndex) {
$this->fixCasing($this::CLASS_CONST_SUPPORTED_HINTS, $tokens, $nativeTypeHintIndex);
}
}

continue;
}

if ('property' === $element['type']) {
foreach ($this->getNativeTypeHintCandidatesForProperty($tokens, $index) as $nativeTypeHintIndex) {
$this->fixCasing($this::CLASS_PROPERTY_SUPPORTED_HINTS, $tokens, $nativeTypeHintIndex);
}
}
}
}


private function getNativeTypeHintCandidatesForConstant(Tokens $tokens, int $index): iterable
{
$constNameIndex = $this->getConstNameIndex($tokens, $index);
$index = $this->getFirstIndexOfType($tokens, $index);

do {
$typeEnd = $this->getTypeEnd($tokens, $index, $constNameIndex);

if ($typeEnd === $index) {
yield $index;
}

do {
$index = $tokens->getNextMeaningfulToken($index);
} while ($tokens[$index]->isGivenKind(self::TYPE_SEPARATION_TYPES));
} while ($index < $constNameIndex);
}

private function isConstWithoutType(Tokens $tokens, int $index): bool
{
$index = $tokens->getNextMeaningfulToken($index);

return $tokens[$index]->isGivenKind(T_STRING) && $tokens[$tokens->getNextMeaningfulToken($index)]->equals('=');
}

private function getConstNameIndex(Tokens $tokens, int $index): int
{
return $tokens->getPrevMeaningfulToken(
$tokens->getNextTokenOfKind($index, ['=']),
);
}


private function getNativeTypeHintCandidatesForProperty(Tokens $tokens, int $index): iterable
{
$propertyNameIndex = $index;
$index = $tokens->getPrevTokenOfKind($index, $this->beforePropertyTypeTokens);

$index = $this->getFirstIndexOfType($tokens, $index);

do {
$typeEnd = $this->getTypeEnd($tokens, $index, $propertyNameIndex);

if ($typeEnd === $index) {
yield $index;
}

do {
$index = $tokens->getNextMeaningfulToken($index);
} while ($tokens[$index]->isGivenKind(self::TYPE_SEPARATION_TYPES));
} while ($index < $propertyNameIndex);

return [];
}

private function getFirstIndexOfType(Tokens $tokens, int $index): int
{
$index = $tokens->getNextMeaningfulToken($index);

if ($tokens[$index]->isGivenKind(CT::T_NULLABLE_TYPE)) {
$index = $tokens->getNextMeaningfulToken($index);
}

if ($tokens[$index]->isGivenKind(CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN)) {
$index = $tokens->getNextMeaningfulToken($index);
}

return $index;
}

private function getTypeEnd(Tokens $tokens, int $index, int $upperLimit): int
{
if (!$tokens[$index]->isGivenKind([T_STRING, T_NS_SEPARATOR])) {
return $index; 
}

$endIndex = $index;
while ($tokens[$index]->isGivenKind([T_STRING, T_NS_SEPARATOR]) && $index < $upperLimit) {
$endIndex = $index;
$index = $tokens->getNextMeaningfulToken($index);
}

return $endIndex;
}




private function fixCasing(array $supportedTypeHints, Tokens $tokens, int $index): void
{
$typeContent = $tokens[$index]->getContent();
$typeContentLower = strtolower($typeContent);

if (isset($supportedTypeHints[$typeContentLower]) && $typeContent !== $typeContentLower) {
$tokens[$index] = new Token([$tokens[$index]->getId(), $typeContentLower]);
}
}
}
