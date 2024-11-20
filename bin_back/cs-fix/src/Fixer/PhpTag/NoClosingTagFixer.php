<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\PhpTag;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;






final class NoClosingTagFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'The closing `?>` tag MUST be omitted from files containing only PHP.',
[new CodeSample("<?php\nclass Sample\n{\n}\n?>\n")]
);
}

public function isCandidate(Tokens $tokens): bool
{
return \count($tokens) >= 2 && $tokens->isMonolithicPhp() && $tokens->isTokenKindFound(T_CLOSE_TAG);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$closeTags = $tokens->findGivenKind(T_CLOSE_TAG);
$index = array_key_first($closeTags);

if (isset($tokens[$index - 1]) && $tokens[$index - 1]->isWhitespace()) {
$tokens->clearAt($index - 1);
}
$tokens->clearAt($index);

$prevIndex = $tokens->getPrevMeaningfulToken($index);
if (!$tokens[$prevIndex]->equalsAny([';', '}', [T_OPEN_TAG]])) {
$tokens->insertAt($prevIndex + 1, new Token(';'));
}
}
}
