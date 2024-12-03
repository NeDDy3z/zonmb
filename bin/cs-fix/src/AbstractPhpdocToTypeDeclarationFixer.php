<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\DocBlock\Annotation;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\TypeExpression;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
@phpstan-type
@phpstan-type
@phpstan-type
@implements









*/
abstract class AbstractPhpdocToTypeDeclarationFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

private const REGEX_CLASS = '(?:\\\?+'.TypeExpression::REGEX_IDENTIFIER
.'(\\\\'.TypeExpression::REGEX_IDENTIFIER.')*+)';




private array $versionSpecificTypes = [
'void' => 7_01_00,
'iterable' => 7_01_00,
'object' => 7_02_00,
'mixed' => 8_00_00,
'never' => 8_01_00,
];




private array $scalarTypes = [
'bool' => true,
'float' => true,
'int' => true,
'string' => true,
];




private static array $syntaxValidationCache = [];

public function isRisky(): bool
{
return true;
}

abstract protected function isSkippedType(string $type): bool;

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('scalar_types', 'Fix also scalar types; may have unexpected behaviour due to PHP bad type coercion system.'))
->setAllowedTypes(['bool'])
->setDefault(true)
->getOption(),
(new FixerOptionBuilder('union_types', 'Fix also union types; turned on by default on PHP >= 8.0.0.'))
->setAllowedTypes(['bool'])
->setDefault(\PHP_VERSION_ID >= 8_00_00)
->getOption(),
]);
}




protected function findFunctionDocComment(Tokens $tokens, int $index): ?int
{
do {
$index = $tokens->getPrevNonWhitespace($index);
} while ($tokens[$index]->isGivenKind([
T_COMMENT,
T_ABSTRACT,
T_FINAL,
T_PRIVATE,
T_PROTECTED,
T_PUBLIC,
T_STATIC,
]));

if ($tokens[$index]->isGivenKind(T_DOC_COMMENT)) {
return $index;
}

return null;
}




protected function getAnnotationsFromDocComment(string $name, Tokens $tokens, int $docCommentIndex): array
{
$namespacesAnalyzer = new NamespacesAnalyzer();
$namespace = $namespacesAnalyzer->getNamespaceAt($tokens, $docCommentIndex);

$namespaceUsesAnalyzer = new NamespaceUsesAnalyzer();
$namespaceUses = $namespaceUsesAnalyzer->getDeclarationsInNamespace($tokens, $namespace);

$doc = new DocBlock(
$tokens[$docCommentIndex]->getContent(),
$namespace,
$namespaceUses
);

return $doc->getAnnotationsOfType($name);
}




protected function createTypeDeclarationTokens(string $type, bool $isNullable): array
{
$newTokens = [];

if (true === $isNullable && 'mixed' !== $type) {
$newTokens[] = new Token([CT::T_NULLABLE_TYPE, '?']);
}

$newTokens = array_merge(
$newTokens,
$this->createTokensFromRawType($type)->toArray()
);


foreach ($newTokens as $i => $token) {
if ($token->isGivenKind(T_STRING)) {
$typeUnqualified = $token->getContent();

if (
(isset($this->scalarTypes[$typeUnqualified]) || isset($this->versionSpecificTypes[$typeUnqualified]))
&& isset($newTokens[$i - 1])
&& '\\' === $newTokens[$i - 1]->getContent()
) {
unset($newTokens[$i - 1]);
}
}
}

return array_values($newTokens);
}








abstract protected function createTokensFromRawType(string $type): Tokens;




protected function getCommonTypeInfo(TypeExpression $typesExpression, bool $isReturnType): ?array
{
$commonType = $typesExpression->getCommonType();
$isNullable = $typesExpression->allowsNull();

if (null === $commonType) {
return null;
}

if ($isNullable && 'void' === $commonType) {
return null;
}

if ('static' === $commonType && (!$isReturnType || \PHP_VERSION_ID < 8_00_00)) {
$commonType = 'self';
}

if ($this->isSkippedType($commonType)) {
return null;
}

if (isset($this->versionSpecificTypes[$commonType]) && \PHP_VERSION_ID < $this->versionSpecificTypes[$commonType]) {
return null;
}

if (isset($this->scalarTypes[$commonType])) {
if (false === $this->configuration['scalar_types']) {
return null;
}
} elseif (!Preg::match('/^'.self::REGEX_CLASS.'$/', $commonType)) {
return null;
}

return ['commonType' => $commonType, 'isNullable' => $isNullable];
}

protected function getUnionTypes(TypeExpression $typesExpression, bool $isReturnType): ?string
{
if (\PHP_VERSION_ID < 8_00_00) {
return null;
}

if (!$typesExpression->isUnionType() || '|' !== $typesExpression->getTypesGlue()) {
return null;
}

if (false === $this->configuration['union_types']) {
return null;
}

$types = $typesExpression->getTypes();
$isNullable = $typesExpression->allowsNull();
$unionTypes = [];
$containsOtherThanIterableType = false;
$containsOtherThanEmptyType = false;

foreach ($types as $type) {
if ('null' === $type) {
continue;
}

if ($this->isSkippedType($type)) {
return null;
}

if (isset($this->versionSpecificTypes[$type]) && \PHP_VERSION_ID < $this->versionSpecificTypes[$type]) {
return null;
}

$typeExpression = new TypeExpression($type, null, []);
$commonType = $typeExpression->getCommonType();

if (!$containsOtherThanIterableType && !\in_array($commonType, ['array', \Traversable::class, 'iterable'], true)) {
$containsOtherThanIterableType = true;
}
if ($isReturnType && !$containsOtherThanEmptyType && !\in_array($commonType, ['null', 'void', 'never'], true)) {
$containsOtherThanEmptyType = true;
}

if (!$isNullable && $typesExpression->allowsNull()) {
$isNullable = true;
}

$unionTypes[] = $commonType;
}

if (!$containsOtherThanIterableType) {
return null;
}
if ($isReturnType && !$containsOtherThanEmptyType) {
return null;
}

if ($isNullable) {
$unionTypes[] = 'null';
}

return implode($typesExpression->getTypesGlue(), array_unique($unionTypes));
}

final protected function isValidSyntax(string $code): bool
{
if (!isset(self::$syntaxValidationCache[$code])) {
try {
Tokens::fromCode($code);
self::$syntaxValidationCache[$code] = true;
} catch (\ParseError $e) {
self::$syntaxValidationCache[$code] = false;
}
}

return self::$syntaxValidationCache[$code];
}
}
