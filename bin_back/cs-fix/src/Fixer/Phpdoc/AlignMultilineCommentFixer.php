<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
@implements
@phpstan-type
@phpstan-type








*/
final class AlignMultilineCommentFixer extends AbstractFixer implements ConfigurableFixerInterface, WhitespacesAwareFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




private $tokenKinds;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Each line of multi-line DocComments must have an asterisk [PSR-5] and must be aligned with the first one.',
[
new CodeSample(
'<?php
    /**
            * This is a DOC Comment
with a line not prefixed with asterisk

   */
'
),
new CodeSample(
'<?php
    /*
            * This is a doc-like multiline comment
*/
',
['comment_type' => 'phpdocs_like']
),
new CodeSample(
'<?php
    /*
            * This is a doc-like multiline comment
with a line not prefixed with asterisk

   */
',
['comment_type' => 'all_multiline']
),
]
);
}







public function getPriority(): int
{
return 27;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound($this->tokenKinds);
}

protected function configurePostNormalisation(): void
{
$this->tokenKinds = [T_DOC_COMMENT];
if ('phpdocs_only' !== $this->configuration['comment_type']) {
$this->tokenKinds[] = T_COMMENT;
}
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$lineEnding = $this->whitespacesConfig->getLineEnding();
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind($this->tokenKinds)) {
continue;
}

$whitespace = '';
$previousIndex = $index - 1;

if ($tokens[$previousIndex]->isWhitespace()) {
$whitespace = $tokens[$previousIndex]->getContent();
--$previousIndex;
}

if ($tokens[$previousIndex]->isGivenKind(T_OPEN_TAG)) {
$whitespace = Preg::replace('/\S/', '', $tokens[$previousIndex]->getContent()).$whitespace;
}

if (!Preg::match('/\R(\h*)$/', $whitespace, $matches)) {
continue;
}

if ($token->isGivenKind(T_COMMENT) && 'all_multiline' !== $this->configuration['comment_type'] && Preg::match('/\R(?:\R|\s*[^\s\*])/', $token->getContent())) {
continue;
}

$indentation = $matches[1];
$lines = Preg::split('/\R/u', $token->getContent());

foreach ($lines as $lineNumber => $line) {
if (0 === $lineNumber) {
continue;
}

$line = ltrim($line);

if ($token->isGivenKind(T_COMMENT) && (!isset($line[0]) || '*' !== $line[0])) {
continue;
}

if (!isset($line[0])) {
$line = '*';
} elseif ('*' !== $line[0]) {
$line = '* '.$line;
}

$lines[$lineNumber] = $indentation.' '.$line;
}

$tokens[$index] = new Token([$token->getId(), implode($lineEnding, $lines)]);
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('comment_type', 'Whether to fix PHPDoc comments only (`phpdocs_only`), any multi-line comment whose lines all start with an asterisk (`phpdocs_like`) or any multi-line comment (`all_multiline`).'))
->setAllowedValues(['phpdocs_only', 'phpdocs_like', 'all_multiline'])
->setDefault('phpdocs_only')
->getOption(),
]);
}
}
