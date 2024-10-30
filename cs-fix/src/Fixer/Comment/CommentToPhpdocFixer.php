<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Comment;

use PhpCsFixer\AbstractFixer;
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
use PhpCsFixer\Tokenizer\Analyzer\CommentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Utils;

/**
@implements
@phpstan-type
@phpstan-type







*/
final class CommentToPhpdocFixer extends AbstractFixer implements ConfigurableFixerInterface, WhitespacesAwareFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




private array $ignoredTags = [];

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_COMMENT);
}

public function isRisky(): bool
{
return true;
}







public function getPriority(): int
{

return 26;
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Comments with annotation should be docblock when used on structural elements.',
[
new CodeSample("<?php /* header */ \$x = true; /* @var bool \$isFoo */ \$isFoo = true;\n"),
new CodeSample("<?php\n// @todo do something later\n\$foo = 1;\n\n// @var int \$a\n\$a = foo();\n", ['ignored_tags' => ['todo']]),
],
null,
'Risky as new docblocks might mean more, e.g. a Doctrine entity might have a new column in database.'
);
}

protected function configurePostNormalisation(): void
{
$this->ignoredTags = array_map(
static fn (string $tag): string => strtolower($tag),
$this->configuration['ignored_tags']
);
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('ignored_tags', 'List of ignored tags.'))
->setAllowedTypes(['string[]'])
->setDefault([])
->getOption(),
]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$commentsAnalyzer = new CommentsAnalyzer();

for ($index = 0, $limit = \count($tokens); $index < $limit; ++$index) {
$token = $tokens[$index];

if (!$token->isGivenKind(T_COMMENT)) {
continue;
}

if ($commentsAnalyzer->isHeaderComment($tokens, $index)) {
continue;
}

if (!$commentsAnalyzer->isBeforeStructuralElement($tokens, $index)) {
continue;
}

$commentIndices = $commentsAnalyzer->getCommentBlockIndices($tokens, $index);

if ($this->isCommentCandidate($tokens, $commentIndices)) {
$this->fixComment($tokens, $commentIndices);
}

$index = max($commentIndices);
}
}




private function isCommentCandidate(Tokens $tokens, array $indices): bool
{
return array_reduce(
$indices,
function (bool $carry, int $index) use ($tokens): bool {
if ($carry) {
return true;
}
if (!Preg::match('~(?:#|//|/\*+|\R(?:\s*\*)?)\s*\@([a-zA-Z0-9_\\\-]+)(?=\s|\(|$)~', $tokens[$index]->getContent(), $matches)) {
return false;
}

return !\in_array(strtolower($matches[1]), $this->ignoredTags, true);
},
false
);
}




private function fixComment(Tokens $tokens, array $indices): void
{
if (1 === \count($indices)) {
$this->fixCommentSingleLine($tokens, $indices[0]);
} else {
$this->fixCommentMultiLine($tokens, $indices);
}
}

private function fixCommentSingleLine(Tokens $tokens, int $index): void
{
$message = $this->getMessage($tokens[$index]->getContent());

if ('' !== trim(substr($message, 0, 1))) {
$message = ' '.$message;
}

if ('' !== trim(substr($message, -1))) {
$message .= ' ';
}

$tokens[$index] = new Token([T_DOC_COMMENT, '/**'.$message.'*/']);
}




private function fixCommentMultiLine(Tokens $tokens, array $indices): void
{
$startIndex = $indices[0];
$indent = Utils::calculateTrailingWhitespaceIndent($tokens[$startIndex - 1]);

$newContent = '/**'.$this->whitespacesConfig->getLineEnding();
$count = max($indices);

for ($index = $startIndex; $index <= $count; ++$index) {
if (!$tokens[$index]->isComment()) {
continue;
}
if (str_contains($tokens[$index]->getContent(), '*/')) {
return;
}
$message = $this->getMessage($tokens[$index]->getContent());
if ('' !== trim(substr($message, 0, 1))) {
$message = ' '.$message;
}
$newContent .= $indent.' *'.$message.$this->whitespacesConfig->getLineEnding();
}

for ($index = $startIndex; $index <= $count; ++$index) {
$tokens->clearAt($index);
}

$newContent .= $indent.' */';

$tokens->insertAt($startIndex, new Token([T_DOC_COMMENT, $newContent]));
}

private function getMessage(string $content): string
{
if (str_starts_with($content, '#')) {
return substr($content, 1);
}
if (str_starts_with($content, '//')) {
return substr($content, 2);
}

return rtrim(ltrim($content, '/*'), '*/');
}
}
