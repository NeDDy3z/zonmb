<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
@implements
@phpstan-type
@phpstan-type







*/
final class TypesSpacesFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'A single space or none should be around union type and intersection type operators.',
[
new CodeSample(
"<?php\ntry\n{\n    new Foo();\n} catch (ErrorA | ErrorB \$e) {\necho'error';}\n"
),
new CodeSample(
"<?php\ntry\n{\n    new Foo();\n} catch (ErrorA|ErrorB \$e) {\necho'error';}\n",
['space' => 'single']
),
new VersionSpecificCodeSample(
"<?php\nfunction foo(int | string \$x)\n{\n}\n",
new VersionSpecification(8_00_00)
),
]
);
}






public function getPriority(): int
{
return -1;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound([CT::T_TYPE_ALTERNATION, CT::T_TYPE_INTERSECTION]);
}

protected function configurePostNormalisation(): void
{
if (!isset($this->configuration['space_multiple_catch'])) {
$this->configuration = [
'space' => $this->configuration['space'],
'space_multiple_catch' => $this->configuration['space'],
];
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('space', 'Spacing to apply around union type and intersection type operators.'))
->setAllowedValues(['none', 'single'])
->setDefault('none')
->getOption(),
(new FixerOptionBuilder('space_multiple_catch', 'Spacing to apply around type operator when catching exceptions of multiple types, use `null` to follow the value configured for `space`.'))
->setAllowedValues(['none', 'single', null])
->setDefault(null)
->getOption(),
]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$tokenCount = $tokens->count() - 1;

for ($index = 0; $index < $tokenCount; ++$index) {
if ($tokens[$index]->isGivenKind([CT::T_TYPE_ALTERNATION, CT::T_TYPE_INTERSECTION])) {
$tokenCount += $this->fixSpacing($tokens, $index, 'single' === $this->configuration['space']);

continue;
}

if ($tokens[$index]->isGivenKind(T_CATCH)) {
while (true) {
$index = $tokens->getNextTokenOfKind($index, [')', [CT::T_TYPE_ALTERNATION]]);

if ($tokens[$index]->equals(')')) {
break;
}

$tokenCount += $this->fixSpacing($tokens, $index, 'single' === $this->configuration['space_multiple_catch']);
}


}
}
}

private function fixSpacing(Tokens $tokens, int $index, bool $singleSpace): int
{
if (!$singleSpace) {
$this->ensureNoSpace($tokens, $index + 1);
$this->ensureNoSpace($tokens, $index - 1);

return 0;
}

$addedTokenCount = 0;
$addedTokenCount += $this->ensureSingleSpace($tokens, $index + 1, 0);
$addedTokenCount += $this->ensureSingleSpace($tokens, $index - 1, 1);

return $addedTokenCount;
}

private function ensureSingleSpace(Tokens $tokens, int $index, int $offset): int
{
if (!$tokens[$index]->isWhitespace()) {
$tokens->insertSlices([$index + $offset => new Token([T_WHITESPACE, ' '])]);

return 1;
}

if (' ' !== $tokens[$index]->getContent() && !Preg::match('/\R/', $tokens[$index]->getContent())) {
$tokens[$index] = new Token([T_WHITESPACE, ' ']);
}

return 0;
}

private function ensureNoSpace(Tokens $tokens, int $index): void
{
if ($tokens[$index]->isWhitespace() && !Preg::match('/\R/', $tokens[$index]->getContent())) {
$tokens->clearAt($index);
}
}
}
