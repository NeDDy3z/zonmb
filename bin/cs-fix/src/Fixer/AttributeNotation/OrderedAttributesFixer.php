<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\AttributeNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\AttributeAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\AttributeAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symfony\Component\OptionsResolver\Options;

/**
@phpstan-import-type
@implements
@phpstan-type
@phpstan-type










*/
final class OrderedAttributesFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public const ORDER_ALPHA = 'alpha';
public const ORDER_CUSTOM = 'custom';

private const SUPPORTED_SORT_ALGORITHMS = [
self::ORDER_ALPHA,
self::ORDER_CUSTOM,
];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Sorts attributes using the configured sort algorithm.',
[
new VersionSpecificCodeSample(
<<<'EOL'
                        <?php

                        #[Foo]
                        #[Bar(3)]
                        #[Qux(new Bar(5))]
                        #[Corge(a: 'test')]
                        class Sample1 {}

                        #[
                            Foo,
                            Bar(3),
                            Qux(new Bar(5)),
                            Corge(a: 'test'),
                        ]
                        class Sample2 {}

                        EOL,
new VersionSpecification(8_00_00),
),
new VersionSpecificCodeSample(
<<<'EOL'
                        <?php

                        use A\B\Foo;
                        use A\B\Bar as BarAlias;
                        use A\B as AB;

                        #[Foo]
                        #[BarAlias(3)]
                        #[AB\Qux(new Bar(5))]
                        #[\A\B\Corge(a: 'test')]
                        class Sample1 {}

                        EOL,
new VersionSpecification(8_00_00),
['sort_algorithm' => self::ORDER_CUSTOM, 'order' => ['A\B\Qux', 'A\B\Bar', 'A\B\Corge']],
),
],
);
}






public function getPriority(): int
{
return 0;
}

public function isCandidate(Tokens $tokens): bool
{
return \defined('T_ATTRIBUTE') && $tokens->isTokenKindFound(T_ATTRIBUTE);
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
$fixerName = $this->getName();

return new FixerConfigurationResolver([
(new FixerOptionBuilder('sort_algorithm', 'How the attributes should be sorted.'))
->setAllowedValues(self::SUPPORTED_SORT_ALGORITHMS)
->setDefault(self::ORDER_ALPHA)
->setNormalizer(static function (Options $options, string $value) use ($fixerName): string {
if (self::ORDER_CUSTOM === $value && [] === $options['order']) {
throw new InvalidFixerConfigurationException(
$fixerName,
'The custom order strategy requires providing `order` option with a list of attributes\'s FQNs.'
);
}

return $value;
})
->getOption(),
(new FixerOptionBuilder('order', 'A list of FQCNs of attributes defining the desired order used when custom sorting algorithm is configured.'))
->setAllowedTypes(['string[]'])
->setDefault([])
->setNormalizer(static function (Options $options, array $value) use ($fixerName): array {
if ($value !== array_unique($value)) {
throw new InvalidFixerConfigurationException($fixerName, 'The list includes attributes that are not unique.');
}

return array_flip(array_values(
array_map(static fn (string $attribute): string => ltrim($attribute, '\\'), $value),
));
})
->getOption(),
]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$index = 0;

while (null !== $index = $tokens->getNextTokenOfKind($index, [[T_ATTRIBUTE]])) {

$elements = array_map(function (AttributeAnalysis $attributeAnalysis) use ($tokens): array {
return [
'name' => $this->sortAttributes($tokens, $attributeAnalysis->getStartIndex(), $attributeAnalysis->getAttributes()),
'start' => $attributeAnalysis->getStartIndex(),
'end' => $attributeAnalysis->getEndIndex(),
];
}, AttributeAnalyzer::collect($tokens, $index));

$endIndex = end($elements)['end'];

try {
if (1 === \count($elements)) {
continue;
}

$sortedElements = $this->sortElements($elements);

if ($elements === $sortedElements) {
continue;
}

$this->sortTokens($tokens, $index, $endIndex, $sortedElements);
} finally {
$index = $endIndex;
}
}
}




private function sortAttributes(Tokens $tokens, int $index, array $attributes): string
{
if (1 === \count($attributes)) {
return $this->getAttributeName($tokens, $attributes[0]['name'], $attributes[0]['start']);
}

foreach ($attributes as &$attribute) {
$attribute['name'] = $this->getAttributeName($tokens, $attribute['name'], $attribute['start']);
}

$sortedElements = $this->sortElements($attributes);

if ($attributes === $sortedElements) {
return $attributes[0]['name'];
}

$this->sortTokens($tokens, $index + 1, end($attributes)['end'], $sortedElements, new Token(','));

return $sortedElements[0]['name'];
}

private function getAttributeName(Tokens $tokens, string $name, int $index): string
{
if (self::ORDER_CUSTOM === $this->configuration['sort_algorithm']) {
$name = $this->determineAttributeFullyQualifiedName($tokens, $name, $index);
}

return ltrim($name, '\\');
}

private function determineAttributeFullyQualifiedName(Tokens $tokens, string $name, int $index): string
{
if ('\\' === $name[0]) {
return $name;
}

if (!$tokens[$index]->isGivenKind([T_STRING, T_NS_SEPARATOR])) {
$index = $tokens->getNextTokenOfKind($index, [[T_STRING], [T_NS_SEPARATOR]]);
}

[$namespaceAnalysis, $namespaceUseAnalyses] = $this->collectNamespaceAnalysis($tokens, $index);
$namespace = $namespaceAnalysis->getFullName();
$firstTokenOfName = $tokens[$index]->getContent();
$namespaceUseAnalysis = $namespaceUseAnalyses[$firstTokenOfName] ?? false;

if ($namespaceUseAnalysis instanceof NamespaceUseAnalysis) {
$namespace = $namespaceUseAnalysis->getFullName();

if ($name === $firstTokenOfName) {
return $namespace;
}

$name = substr(strstr($name, '\\'), 1);
}

return $namespace.'\\'.$name;
}






private function sortElements(array $elements): array
{
usort($elements, function (array $a, array $b): int {
$sortAlgorithm = $this->configuration['sort_algorithm'];

if (self::ORDER_ALPHA === $sortAlgorithm) {
return $a['name'] <=> $b['name'];
}

if (self::ORDER_CUSTOM === $sortAlgorithm) {
return
($this->configuration['order'][$a['name']] ?? PHP_INT_MAX)
<=>
($this->configuration['order'][$b['name']] ?? PHP_INT_MAX);
}

throw new \InvalidArgumentException(\sprintf('Invalid sort algorithm "%s" provided.', $sortAlgorithm));
});

return $elements;
}




private function sortTokens(Tokens $tokens, int $startIndex, int $endIndex, array $elements, ?Token $delimiter = null): void
{
$replaceTokens = [];

foreach ($elements as $pos => $element) {
for ($i = $element['start']; $i <= $element['end']; ++$i) {
$replaceTokens[] = clone $tokens[$i];
}
if (null !== $delimiter && $pos !== \count($elements) - 1) {
$replaceTokens[] = clone $delimiter;
}
}

$tokens->overrideRange($startIndex, $endIndex, $replaceTokens);
}




private function collectNamespaceAnalysis(Tokens $tokens, int $startIndex): array
{
$namespaceAnalysis = (new NamespacesAnalyzer())->getNamespaceAt($tokens, $startIndex);
$namespaceUseAnalyses = (new NamespaceUsesAnalyzer())->getDeclarationsInNamespace($tokens, $namespaceAnalysis);

$uses = [];
foreach ($namespaceUseAnalyses as $use) {
if (!$use->isClass()) {
continue;
}

$uses[$use->getShortName()] = $use;
}

return [$namespaceAnalysis, $uses];
}
}
