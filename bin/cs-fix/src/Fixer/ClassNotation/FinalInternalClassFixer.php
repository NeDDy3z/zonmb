<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use PhpCsFixer\Utils;
use Symfony\Component\OptionsResolver\Options;

/**
@implements
@phpstan-type
@phpstan-type















*/
final class FinalInternalClassFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

private const DEFAULTS = [
'include' => [
'internal',
],
'exclude' => [
'final',
'Entity',
'ORM\Entity',
'ORM\Mapping\Entity',
'Mapping\Entity',
'Document',
'ODM\Document',
],
];

private bool $checkAttributes;

public function __construct()
{
parent::__construct();

$this->checkAttributes = \PHP_VERSION_ID >= 8_00_00;
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Internal classes should be `final`.',
[
new CodeSample("<?php\n/**\n * @internal\n */\nclass Sample\n{\n}\n"),
new CodeSample(
"<?php\n/**\n * @CUSTOM\n */\nclass A{}\n\n/**\n * @CUSTOM\n * @not-fix\n */\nclass B{}\n",
[
'include' => ['@Custom'],
'exclude' => ['@not-fix'],
]
),
],
null,
'Changing classes to `final` might cause code execution to break.'
);
}







public function getPriority(): int
{
return 67;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_CLASS);
}

public function isRisky(): bool
{
return true;
}

protected function configurePostNormalisation(): void
{
$this->assertConfigHasNoConflicts();
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$tokensAnalyzer = new TokensAnalyzer($tokens);

for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
if (!$tokens[$index]->isGivenKind(T_CLASS) || !$this->isClassCandidate($tokensAnalyzer, $tokens, $index)) {
continue;
}


$tokens->insertSlices([
$index => [
new Token([T_FINAL, 'final']),
new Token([T_WHITESPACE, ' ']),
],
]);
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
$annotationsAsserts = [static function (array $values): bool {
foreach ($values as $value) {
if ('' === $value) {
return false;
}
}

return true;
}];

$annotationsNormalizer = static function (Options $options, array $value): array {
$newValue = [];
foreach ($value as $key) {
if (str_starts_with($key, '@')) {
$key = substr($key, 1);
}

$newValue[strtolower($key)] = true;
}

return $newValue;
};

return new FixerConfigurationResolver([
(new FixerOptionBuilder('annotation_include', 'Class level attribute or annotation tags that must be set in order to fix the class (case insensitive).'))
->setAllowedTypes(['string[]'])
->setAllowedValues($annotationsAsserts)
->setDefault(
array_map(
static fn (string $string) => '@'.$string,
self::DEFAULTS['include'],
),
)
->setNormalizer($annotationsNormalizer)
->setDeprecationMessage('Use `include` to configure PHPDoc annotations tags and attributes.')
->getOption(),
(new FixerOptionBuilder('annotation_exclude', 'Class level attribute or annotation tags that must be omitted to fix the class, even if all of the white list ones are used as well (case insensitive).'))
->setAllowedTypes(['string[]'])
->setAllowedValues($annotationsAsserts)
->setDefault(
array_map(
static fn (string $string) => '@'.$string,
self::DEFAULTS['exclude'],
),
)
->setNormalizer($annotationsNormalizer)
->setDeprecationMessage('Use `exclude` to configure PHPDoc annotations tags and attributes.')
->getOption(),
(new FixerOptionBuilder('include', 'Class level attribute or annotation tags that must be set in order to fix the class (case insensitive).'))
->setAllowedTypes(['string[]'])
->setAllowedValues($annotationsAsserts)
->setDefault(self::DEFAULTS['include'])
->setNormalizer($annotationsNormalizer)
->getOption(),
(new FixerOptionBuilder('exclude', 'Class level attribute or annotation tags that must be omitted to fix the class, even if all of the white list ones are used as well (case insensitive).'))
->setAllowedTypes(['string[]'])
->setAllowedValues($annotationsAsserts)
->setDefault(self::DEFAULTS['exclude'])
->setNormalizer($annotationsNormalizer)
->getOption(),
(new FixerOptionBuilder('consider_absent_docblock_as_internal_class', 'Whether classes without any DocBlock should be fixed to final.'))
->setAllowedTypes(['bool'])
->setDefault(false)
->getOption(),
]);
}




private function isClassCandidate(TokensAnalyzer $tokensAnalyzer, Tokens $tokens, int $index): bool
{
if ($tokensAnalyzer->isAnonymousClass($index)) {
return false;
}

$modifiers = $tokensAnalyzer->getClassyModifiers($index);

if (isset($modifiers['final']) || isset($modifiers['abstract'])) {
return false; 
}

$decisions = [];
$currentIndex = $index;

$acceptTypes = [
CT::T_ATTRIBUTE_CLOSE,
T_DOC_COMMENT,
T_COMMENT, 
];

if (\defined('T_READONLY')) {

$acceptTypes[] = T_READONLY;
}

while ($currentIndex) {
$currentIndex = $tokens->getPrevNonWhitespace($currentIndex);

if (!$tokens[$currentIndex]->isGivenKind($acceptTypes)) {
break;
}

if ($this->checkAttributes && $tokens[$currentIndex]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
$attributeStartIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_ATTRIBUTE, $currentIndex);
$decisions[] = $this->isClassCandidateBasedOnAttribute($tokens, $attributeStartIndex, $currentIndex);

$currentIndex = $attributeStartIndex;
}

if ($tokens[$currentIndex]->isGivenKind([T_DOC_COMMENT])) {
$decisions[] = $this->isClassCandidateBasedOnPhpDoc($tokens, $currentIndex);
}
}

if (\in_array(false, $decisions, true)) {
return false;
}

return \in_array(true, $decisions, true)
|| ([] === $decisions && true === $this->configuration['consider_absent_docblock_as_internal_class']);
}

private function isClassCandidateBasedOnPhpDoc(Tokens $tokens, int $index): ?bool
{
$doc = new DocBlock($tokens[$index]->getContent());
$tags = [];

foreach ($doc->getAnnotations() as $annotation) {
if (!Preg::match('/@([^\(\s]+)/', $annotation->getContent(), $matches)) {
continue;
}
$tag = strtolower(substr(array_shift($matches), 1));

$tags[$tag] = true;
}

if (\count(array_intersect_key($this->configuration['exclude'], $tags)) > 0) {
return false;
}

if ($this->isConfiguredAsInclude($tags)) {
return true;
}

return null;
}

private function isClassCandidateBasedOnAttribute(Tokens $tokens, int $startIndex, int $endIndex): ?bool
{
$attributeCandidates = [];
$attributeString = '';
$currentIndex = $startIndex;

while ($currentIndex < $endIndex && null !== ($currentIndex = $tokens->getNextMeaningfulToken($currentIndex))) {
if (!$tokens[$currentIndex]->isGivenKind([T_STRING, T_NS_SEPARATOR])) {
if ('' !== $attributeString) {
$attributeCandidates[$attributeString] = true;
$attributeString = '';
}

continue;
}

$attributeString .= strtolower($tokens[$currentIndex]->getContent());
}

if (\count(array_intersect_key($this->configuration['exclude'], $attributeCandidates)) > 0) {
return false;
}

if ($this->isConfiguredAsInclude($attributeCandidates)) {
return true;
}

return null;
}




private function isConfiguredAsInclude(array $attributes): bool
{
if (0 === \count($this->configuration['include'])) {
return true;
}

return \count(array_intersect_key($this->configuration['include'], $attributes)) > 0;
}

private function assertConfigHasNoConflicts(): void
{
foreach (['include' => 'annotation_include', 'exclude' => 'annotation_exclude'] as $newConfigKey => $oldConfigKey) {
$defaults = [];

foreach (self::DEFAULTS[$newConfigKey] as $foo) {
$defaults[strtolower($foo)] = true;
}

$newConfigIsSet = $this->configuration[$newConfigKey] !== $defaults;
$oldConfigIsSet = $this->configuration[$oldConfigKey] !== $defaults;

if ($newConfigIsSet && $oldConfigIsSet) {
throw new InvalidFixerConfigurationException($this->getName(), \sprintf('Configuration cannot contain deprecated option "%s" and new option "%s".', $oldConfigKey, $newConfigKey));
}

if ($oldConfigIsSet) {
$this->configuration[$newConfigKey] = $this->configuration[$oldConfigKey]; 
$this->checkAttributes = false; 
}




unset($this->configuration[$oldConfigKey]); 
}

$intersect = array_intersect_assoc($this->configuration['include'], $this->configuration['exclude']);

if (\count($intersect) > 0) {
throw new InvalidFixerConfigurationException($this->getName(), \sprintf('Annotation cannot be used in both "include" and "exclude" list, got duplicates: %s.', Utils::naturalLanguageJoin(array_keys($intersect))));
}
}
}
