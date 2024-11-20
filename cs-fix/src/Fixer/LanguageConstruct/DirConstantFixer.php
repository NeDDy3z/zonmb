<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\LanguageConstruct;

use PhpCsFixer\AbstractFunctionReferenceFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class DirConstantFixer extends AbstractFunctionReferenceFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Replaces `dirname(__FILE__)` expression with equivalent `__DIR__` constant.',
[new CodeSample("<?php\n\$a = dirname(__FILE__);\n")],
null,
'Risky when the function `dirname` is overridden.'
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound([T_STRING, T_FILE]);
}






public function getPriority(): int
{
return 40;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$currIndex = 0;

do {
$boundaries = $this->find('dirname', $tokens, $currIndex, $tokens->count() - 1);
if (null === $boundaries) {
return;
}

[$functionNameIndex, $openParenthesis, $closeParenthesis] = $boundaries;


$currIndex = $openParenthesis;



$fileCandidateRightIndex = $tokens->getPrevMeaningfulToken($closeParenthesis);
$trailingCommaIndex = null;

if ($tokens[$fileCandidateRightIndex]->equals(',')) {
$trailingCommaIndex = $fileCandidateRightIndex;
$fileCandidateRightIndex = $tokens->getPrevMeaningfulToken($fileCandidateRightIndex);
}

$fileCandidateRight = $tokens[$fileCandidateRightIndex];

if (!$fileCandidateRight->isGivenKind(T_FILE)) {
continue;
}

$fileCandidateLeftIndex = $tokens->getNextMeaningfulToken($openParenthesis);
$fileCandidateLeft = $tokens[$fileCandidateLeftIndex];

if (!$fileCandidateLeft->isGivenKind(T_FILE)) {
continue;
}


$namespaceCandidateIndex = $tokens->getPrevMeaningfulToken($functionNameIndex);
$namespaceCandidate = $tokens[$namespaceCandidateIndex];

if ($namespaceCandidate->isGivenKind(T_NS_SEPARATOR)) {
$tokens->removeTrailingWhitespace($namespaceCandidateIndex);
$tokens->clearAt($namespaceCandidateIndex);
}

if (null !== $trailingCommaIndex) {
if (!$tokens[$tokens->getNextNonWhitespace($trailingCommaIndex)]->isComment()) {
$tokens->removeTrailingWhitespace($trailingCommaIndex);
}

$tokens->clearTokenAndMergeSurroundingWhitespace($trailingCommaIndex);
}


if (!$tokens[$tokens->getNextNonWhitespace($closeParenthesis)]->isComment()) {
$tokens->removeLeadingWhitespace($closeParenthesis);
}

$tokens->clearTokenAndMergeSurroundingWhitespace($closeParenthesis);


if (!$tokens[$tokens->getNextNonWhitespace($openParenthesis)]->isComment()) {
$tokens->removeLeadingWhitespace($openParenthesis);
}

$tokens->removeTrailingWhitespace($openParenthesis);
$tokens->clearTokenAndMergeSurroundingWhitespace($openParenthesis);


$tokens[$fileCandidateLeftIndex] = new Token([T_DIR, '__DIR__']);
$tokens->clearTokenAndMergeSurroundingWhitespace($functionNameIndex);
} while (null !== $currIndex);
}
}
