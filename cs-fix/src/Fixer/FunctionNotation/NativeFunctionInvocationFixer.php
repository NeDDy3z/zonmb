<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Utils;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
@implements
@phpstan-type
@phpstan-type













*/
final class NativeFunctionInvocationFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




public const SET_ALL = '@all';











public const SET_COMPILER_OPTIMIZED = '@compiler_optimized';




public const SET_INTERNAL = '@internal';




private $functionFilter;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Add leading `\` before function invocation to speed up resolving.',
[
new CodeSample(
'<?php

function baz($options)
{
    if (!array_key_exists("foo", $options)) {
        throw new \InvalidArgumentException();
    }

    return json_encode($options);
}
'
),
new CodeSample(
'<?php

function baz($options)
{
    if (!array_key_exists("foo", $options)) {
        throw new \InvalidArgumentException();
    }

    return json_encode($options);
}
',
[
'exclude' => [
'json_encode',
],
]
),
new CodeSample(
'<?php
namespace space1 {
    echo count([1]);
}
namespace {
    echo count([1]);
}
',
['scope' => 'all']
),
new CodeSample(
'<?php
namespace space1 {
    echo count([1]);
}
namespace {
    echo count([1]);
}
',
['scope' => 'namespaced']
),
new CodeSample(
'<?php
myGlobalFunction();
count();
',
['include' => ['myGlobalFunction']]
),
new CodeSample(
'<?php
myGlobalFunction();
count();
',
['include' => ['@all']]
),
new CodeSample(
'<?php
myGlobalFunction();
count();
',
['include' => ['@internal']]
),
new CodeSample(
'<?php
$a .= str_repeat($a, 4);
$c = get_class($d);
',
['include' => ['@compiler_optimized']]
),
],
null,
'Risky when any of the functions are overridden.'
);
}







public function getPriority(): int
{
return 1;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_STRING);
}

public function isRisky(): bool
{
return true;
}

protected function configurePostNormalisation(): void
{
$this->functionFilter = $this->getFunctionFilter();
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
if ('all' === $this->configuration['scope']) {
$this->fixFunctionCalls($tokens, $this->functionFilter, 0, \count($tokens) - 1, false);

return;
}

$namespaces = $tokens->getNamespaceDeclarations();



foreach (array_reverse($namespaces) as $namespace) {
$this->fixFunctionCalls($tokens, $this->functionFilter, $namespace->getScopeStartIndex(), $namespace->getScopeEndIndex(), $namespace->isGlobalNamespace());
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('exclude', 'List of functions to ignore.'))
->setAllowedTypes(['string[]'])
->setAllowedValues([static function (array $value): bool {
foreach ($value as $functionName) {
if ('' === trim($functionName) || trim($functionName) !== $functionName) {
throw new InvalidOptionsException(\sprintf(
'Each element must be a non-empty, trimmed string, got "%s" instead.',
get_debug_type($functionName)
));
}
}

return true;
}])
->setDefault([])
->getOption(),
(new FixerOptionBuilder('include', 'List of function names or sets to fix. Defined sets are `@internal` (all native functions), `@all` (all global functions) and `@compiler_optimized` (functions that are specially optimized by Zend).'))
->setAllowedTypes(['string[]'])
->setAllowedValues([static function (array $value): bool {
foreach ($value as $functionName) {
if ('' === trim($functionName) || trim($functionName) !== $functionName) {
throw new InvalidOptionsException(\sprintf(
'Each element must be a non-empty, trimmed string, got "%s" instead.',
get_debug_type($functionName)
));
}

$sets = [
self::SET_ALL,
self::SET_INTERNAL,
self::SET_COMPILER_OPTIMIZED,
];

if (str_starts_with($functionName, '@') && !\in_array($functionName, $sets, true)) {
throw new InvalidOptionsException(\sprintf('Unknown set "%s", known sets are %s.', $functionName, Utils::naturalLanguageJoin($sets)));
}
}

return true;
}])
->setDefault([self::SET_COMPILER_OPTIMIZED])
->getOption(),
(new FixerOptionBuilder('scope', 'Only fix function calls that are made within a namespace or fix all.'))
->setAllowedValues(['all', 'namespaced'])
->setDefault('all')
->getOption(),
(new FixerOptionBuilder('strict', 'Whether leading `\` of function call not meant to have it should be removed.'))
->setAllowedTypes(['bool'])
->setDefault(true)
->getOption(),
]);
}

private function fixFunctionCalls(Tokens $tokens, callable $functionFilter, int $start, int $end, bool $tryToRemove): void
{
$functionsAnalyzer = new FunctionsAnalyzer();

$tokensToInsert = [];
for ($index = $start; $index < $end; ++$index) {
if (!$functionsAnalyzer->isGlobalFunctionCall($tokens, $index)) {
continue;
}

$prevIndex = $tokens->getPrevMeaningfulToken($index);

if (!$functionFilter($tokens[$index]->getContent()) || $tryToRemove) {
if (false === $this->configuration['strict']) {
continue;
}

if ($tokens[$prevIndex]->isGivenKind(T_NS_SEPARATOR)) {
$tokens->clearTokenAndMergeSurroundingWhitespace($prevIndex);
}

continue;
}

if ($tokens[$prevIndex]->isGivenKind(T_NS_SEPARATOR)) {
continue; 
}

$tokensToInsert[$index] = new Token([T_NS_SEPARATOR, '\\']);
}

$tokens->insertSlices($tokensToInsert);
}

private function getFunctionFilter(): callable
{
$exclude = $this->normalizeFunctionNames($this->configuration['exclude']);

if (\in_array(self::SET_ALL, $this->configuration['include'], true)) {
if (\count($exclude) > 0) {
return static fn (string $functionName): bool => !isset($exclude[strtolower($functionName)]);
}

return static fn (): bool => true;
}

$include = [];

if (\in_array(self::SET_INTERNAL, $this->configuration['include'], true)) {
$include = $this->getAllInternalFunctionsNormalized();
} elseif (\in_array(self::SET_COMPILER_OPTIMIZED, $this->configuration['include'], true)) {
$include = $this->getAllCompilerOptimizedFunctionsNormalized(); 
}

foreach ($this->configuration['include'] as $additional) {
if (!str_starts_with($additional, '@')) {
$include[strtolower($additional)] = true;
}
}

if (\count($exclude) > 0) {
return static fn (string $functionName): bool => isset($include[strtolower($functionName)]) && !isset($exclude[strtolower($functionName)]);
}

return static fn (string $functionName): bool => isset($include[strtolower($functionName)]);
}




private function getAllCompilerOptimizedFunctionsNormalized(): array
{
return $this->normalizeFunctionNames([

'array_key_exists',
'array_slice',
'assert',
'boolval',
'call_user_func',
'call_user_func_array',
'chr',
'count',
'defined',
'doubleval',
'floatval',
'func_get_args',
'func_num_args',
'get_called_class',
'get_class',
'gettype',
'in_array',
'intval',
'is_array',
'is_bool',
'is_double',
'is_float',
'is_int',
'is_integer',
'is_long',
'is_null',
'is_object',
'is_real',
'is_resource',
'is_scalar',
'is_string',
'ord',
'sizeof',
'sprintf',
'strlen',
'strval',



'constant',
'define',
'dirname',
'extension_loaded',
'function_exists',
'is_callable',
'ini_get',
]);
}




private function getAllInternalFunctionsNormalized(): array
{
return $this->normalizeFunctionNames(get_defined_functions()['internal']);
}






private function normalizeFunctionNames(array $functionNames): array
{
$result = [];

foreach ($functionNames as $functionName) {
$result[strtolower($functionName)] = true;
}

return $result;
}
}
