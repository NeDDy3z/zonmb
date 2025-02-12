<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
@implements
@phpstan-type
@phpstan-type







*/
final class PhpdocAddMissingParamAnnotationFixer extends AbstractFixer implements ConfigurableFixerInterface, WhitespacesAwareFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'PHPDoc should contain `@param` for all params.',
[
new CodeSample(
'<?php
/**
 * @param int $bar
 *
 * @return void
 */
function f9(string $foo, $bar, $baz) {}
'
),
new CodeSample(
'<?php
/**
 * @param int $bar
 *
 * @return void
 */
function f9(string $foo, $bar, $baz) {}
',
['only_untyped' => true]
),
new CodeSample(
'<?php
/**
 * @param int $bar
 *
 * @return void
 */
function f9(string $foo, $bar, $baz) {}
',
['only_untyped' => false]
),
]
);
}







public function getPriority(): int
{
return 10;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$argumentsAnalyzer = new ArgumentsAnalyzer();

for ($index = 0, $limit = $tokens->count(); $index < $limit; ++$index) {
$token = $tokens[$index];

if (!$token->isGivenKind(T_DOC_COMMENT)) {
continue;
}

$tokenContent = $token->getContent();

if (false !== stripos($tokenContent, 'inheritdoc')) {
continue;
}


if (!str_contains($tokenContent, "\n")) {
continue;
}

$mainIndex = $index;
$index = $tokens->getNextMeaningfulToken($index);

if (null === $index) {
return;
}

while ($tokens[$index]->isGivenKind([
T_ABSTRACT,
T_FINAL,
T_PRIVATE,
T_PROTECTED,
T_PUBLIC,
T_STATIC,
])) {
$index = $tokens->getNextMeaningfulToken($index);
}

if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
continue;
}

$openIndex = $tokens->getNextTokenOfKind($index, ['(']);
$index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openIndex);

$arguments = [];

foreach ($argumentsAnalyzer->getArguments($tokens, $openIndex, $index) as $start => $end) {
$argumentInfo = $this->prepareArgumentInformation($tokens, $start, $end);

if (false === $this->configuration['only_untyped'] || '' === $argumentInfo['type']) {
$arguments[$argumentInfo['name']] = $argumentInfo;
}
}

if (0 === \count($arguments)) {
continue;
}

$doc = new DocBlock($tokenContent);
$lastParamLine = null;

foreach ($doc->getAnnotationsOfType('param') as $annotation) {
$pregMatched = Preg::match('/^[^$]+(\$\w+).*$/s', $annotation->getContent(), $matches);

if ($pregMatched) {
unset($arguments[$matches[1]]);
}

$lastParamLine = max($lastParamLine, $annotation->getEnd());
}

if (0 === \count($arguments)) {
continue;
}

$lines = $doc->getLines();
$linesCount = \count($lines);

Preg::match('/^(\s*).*$/', $lines[$linesCount - 1]->getContent(), $matches);
$indent = $matches[1];

$newLines = [];

foreach ($arguments as $argument) {
$type = '' !== $argument['type'] ? $argument['type'] : 'mixed';

if (!str_starts_with($type, '?') && 'null' === strtolower($argument['default'])) {
$type = 'null|'.$type;
}

$newLines[] = new Line(\sprintf(
'%s* @param %s %s%s',
$indent,
$type,
$argument['name'],
$this->whitespacesConfig->getLineEnding()
));
}

array_splice(
$lines,
$lastParamLine > 0 ? $lastParamLine + 1 : $linesCount - 1,
0,
$newLines
);

$tokens[$mainIndex] = new Token([T_DOC_COMMENT, implode('', $lines)]);
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('only_untyped', 'Whether to add missing `@param` annotations for untyped parameters only.'))
->setDefault(true)
->setAllowedTypes(['bool'])
->getOption(),
]);
}




private function prepareArgumentInformation(Tokens $tokens, int $start, int $end): array
{
$info = [
'default' => '',
'name' => '',
'type' => '',
];

$sawName = false;

for ($index = $start; $index <= $end; ++$index) {
$token = $tokens[$index];

if (
$token->isComment()
|| $token->isWhitespace()
|| $token->isGivenKind([
CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE,
CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED,
CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC,
])
|| (\defined('T_READONLY') && $token->isGivenKind(T_READONLY))
) {
continue;
}

if ($token->isGivenKind(T_VARIABLE)) {
$sawName = true;
$info['name'] = $token->getContent();

continue;
}

if ($token->equals('=')) {
continue;
}

if ($sawName) {
$info['default'] .= $token->getContent();
} elseif (!$token->equals('&')) {
if ($token->isGivenKind(T_ELLIPSIS)) {
if ('' === $info['type']) {
$info['type'] = 'array';
} else {
$info['type'] .= '[]';
}
} else {
$info['type'] .= $token->getContent();
}
}
}

return $info;
}
}
