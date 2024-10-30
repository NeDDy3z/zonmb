<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ConstantNotation;

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
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
@implements
@phpstan-type
@phpstan-type















*/
final class NativeConstantInvocationFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




private array $constantsToEscape = [];




private array $caseInsensitiveConstantsToEscape = [];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Add leading `\` before constant invocation of internal constant to speed up resolving. Constant name match is case-sensitive, except for `null`, `false` and `true`.',
[
new CodeSample("<?php var_dump(PHP_VERSION, M_PI, MY_CUSTOM_PI);\n"),
new CodeSample(
'<?php
namespace space1 {
    echo PHP_VERSION;
}
namespace {
    echo M_PI;
}
',
['scope' => 'namespaced']
),
new CodeSample(
"<?php var_dump(PHP_VERSION, M_PI, MY_CUSTOM_PI);\n",
[
'include' => [
'MY_CUSTOM_PI',
],
]
),
new CodeSample(
"<?php var_dump(PHP_VERSION, M_PI, MY_CUSTOM_PI);\n",
[
'fix_built_in' => false,
'include' => [
'MY_CUSTOM_PI',
],
]
),
new CodeSample(
"<?php var_dump(PHP_VERSION, M_PI, MY_CUSTOM_PI);\n",
[
'exclude' => [
'M_PI',
],
]
),
],
null,
'Risky when any of the constants are namespaced or overridden.'
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
$uniqueConfiguredExclude = array_unique($this->configuration['exclude']);


$constantsToEscape = array_values($this->configuration['include']);

if (true === $this->configuration['fix_built_in']) {
$getDefinedConstants = get_defined_constants(true);
unset($getDefinedConstants['user']);
foreach ($getDefinedConstants as $constants) {
$constantsToEscape = [...$constantsToEscape, ...array_keys($constants)];
}
}


$constantsToEscape = array_diff(
array_unique($constantsToEscape),
$uniqueConfiguredExclude
);


static $caseInsensitiveConstants = ['null', 'false', 'true'];
$caseInsensitiveConstantsToEscape = [];

foreach ($constantsToEscape as $constantIndex => $constant) {
$loweredConstant = strtolower($constant);
if (\in_array($loweredConstant, $caseInsensitiveConstants, true)) {
$caseInsensitiveConstantsToEscape[] = $loweredConstant;
unset($constantsToEscape[$constantIndex]);
}
}

$caseInsensitiveConstantsToEscape = array_diff(
array_unique($caseInsensitiveConstantsToEscape),
array_map(
static fn (string $function): string => strtolower($function),
$uniqueConfiguredExclude,
),
);


$this->constantsToEscape = array_fill_keys($constantsToEscape, true);
ksort($this->constantsToEscape);

$this->caseInsensitiveConstantsToEscape = array_fill_keys($caseInsensitiveConstantsToEscape, true);
ksort($this->caseInsensitiveConstantsToEscape);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
if ('all' === $this->configuration['scope']) {
$this->fixConstantInvocations($tokens, 0, \count($tokens) - 1);

return;
}

$namespaces = $tokens->getNamespaceDeclarations();



foreach (array_reverse($namespaces) as $namespace) {
if ($namespace->isGlobalNamespace()) {
continue;
}

$this->fixConstantInvocations($tokens, $namespace->getScopeStartIndex(), $namespace->getScopeEndIndex());
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
$constantChecker = static function (array $value): bool {
foreach ($value as $constantName) {
if (trim($constantName) !== $constantName) {
throw new InvalidOptionsException(\sprintf(
'Each element must be a non-empty, trimmed string, got "%s" instead.',
get_debug_type($constantName)
));
}
}

return true;
};

return new FixerConfigurationResolver([
(new FixerOptionBuilder('fix_built_in', 'Whether to fix constants returned by `get_defined_constants`. User constants are not accounted in this list and must be specified in the include one.'))
->setAllowedTypes(['bool'])
->setDefault(true)
->getOption(),
(new FixerOptionBuilder('include', 'List of additional constants to fix.'))
->setAllowedTypes(['string[]'])
->setAllowedValues([$constantChecker])
->setDefault([])
->getOption(),
(new FixerOptionBuilder('exclude', 'List of constants to ignore.'))
->setAllowedTypes(['string[]'])
->setAllowedValues([$constantChecker])
->setDefault(['null', 'false', 'true'])
->getOption(),
(new FixerOptionBuilder('scope', 'Only fix constant invocations that are made within a namespace or fix all.'))
->setAllowedValues(['all', 'namespaced'])
->setDefault('all')
->getOption(),
(new FixerOptionBuilder('strict', 'Whether leading `\` of constant invocation not meant to have it should be removed.'))
->setAllowedTypes(['bool'])
->setDefault(true)
->getOption(),
]);
}

private function fixConstantInvocations(Tokens $tokens, int $startIndex, int $endIndex): void
{
$useDeclarations = (new NamespaceUsesAnalyzer())->getDeclarationsFromTokens($tokens);
$useConstantDeclarations = [];

foreach ($useDeclarations as $use) {
if ($use->isConstant()) {
$useConstantDeclarations[$use->getShortName()] = true;
}
}

$tokenAnalyzer = new TokensAnalyzer($tokens);

for ($index = $endIndex; $index > $startIndex; --$index) {
$token = $tokens[$index];


if (!$token->isGivenKind(T_STRING)) {
continue;
}

if (!$tokenAnalyzer->isConstantInvocation($index)) {
continue;
}

$tokenContent = $token->getContent();
$prevIndex = $tokens->getPrevMeaningfulToken($index);

if (!isset($this->constantsToEscape[$tokenContent]) && !isset($this->caseInsensitiveConstantsToEscape[strtolower($tokenContent)])) {
if (false === $this->configuration['strict']) {
continue;
}

if (!$tokens[$prevIndex]->isGivenKind(T_NS_SEPARATOR)) {
continue;
}

$prevPrevIndex = $tokens->getPrevMeaningfulToken($prevIndex);

if ($tokens[$prevPrevIndex]->isGivenKind(T_STRING)) {
continue;
}

$tokens->clearTokenAndMergeSurroundingWhitespace($prevIndex);

continue;
}

if (isset($useConstantDeclarations[$tokenContent])) {
continue;
}

if ($tokens[$prevIndex]->isGivenKind(T_NS_SEPARATOR)) {
continue;
}

$tokens->insertAt($index, new Token([T_NS_SEPARATOR, '\\']));
}
}
}
