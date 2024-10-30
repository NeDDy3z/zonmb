<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
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

/**
@implements
@phpstan-type
@phpstan-type










*/
final class PhpdocToCommentFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




private array $ignoredTags = [];
private bool $allowBeforeReturnStatement = false;

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}







public function getPriority(): int
{





return 25;
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Docblocks should only be used on structural elements.',
[
new CodeSample(
'<?php
$first = true;// needed because by default first docblock is never fixed.

/** This should be a comment */
foreach($connections as $key => $sqlite) {
    $sqlite->open($path);
}
'
),
new CodeSample(
'<?php
$first = true;// needed because by default first docblock is never fixed.

/** This should be a comment */
foreach($connections as $key => $sqlite) {
    $sqlite->open($path);
}

/** @todo This should be a PHPDoc as the tag is on "ignored_tags" list */
foreach($connections as $key => $sqlite) {
    $sqlite->open($path);
}
',
['ignored_tags' => ['todo']]
),
new CodeSample(
'<?php
$first = true;// needed because by default first docblock is never fixed.

/** This should be a comment */
foreach($connections as $key => $sqlite) {
    $sqlite->open($path);
}

function returnClassName() {
    /** @var class-string */
    return \StdClass::class;
}
',
['allow_before_return_statement' => true]
),
]
);
}

protected function configurePostNormalisation(): void
{
$this->ignoredTags = array_map(
static fn (string $tag): string => strtolower($tag),
$this->configuration['ignored_tags']
);

$this->allowBeforeReturnStatement = true === $this->configuration['allow_before_return_statement'];
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('ignored_tags', 'List of ignored tags (matched case insensitively).'))
->setAllowedTypes(['string[]'])
->setDefault([])
->getOption(),
(new FixerOptionBuilder('allow_before_return_statement', 'Whether to allow PHPDoc before return statement.'))
->setAllowedTypes(['bool'])
->setDefault(false) 
->getOption(),
]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$commentsAnalyzer = new CommentsAnalyzer();

foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_DOC_COMMENT)) {
continue;
}

if ($commentsAnalyzer->isHeaderComment($tokens, $index)) {
continue;
}

if ($this->allowBeforeReturnStatement && $commentsAnalyzer->isBeforeReturn($tokens, $index)) {
continue;
}

if ($commentsAnalyzer->isBeforeStructuralElement($tokens, $index)) {
continue;
}

if (0 < Preg::matchAll('~\@([a-zA-Z0-9_\\\-]+)\b~', $token->getContent(), $matches)) {
foreach ($matches[1] as $match) {
if (\in_array(strtolower($match), $this->ignoredTags, true)) {
continue 2;
}
}
}

$tokens[$index] = new Token([T_COMMENT, '/*'.ltrim($token->getContent(), '/*')]);
}
}
}
