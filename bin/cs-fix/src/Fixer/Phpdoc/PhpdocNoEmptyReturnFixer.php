<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\Annotation;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class PhpdocNoEmptyReturnFixer extends AbstractFixer
{
public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'`@return void` and `@return null` annotations should be omitted from PHPDoc.',
[
new CodeSample(
'<?php
/**
 * @return null
*/
function foo() {}
'
),
new CodeSample(
'<?php
/**
 * @return void
*/
function foo() {}
'
),
]
);
}







public function getPriority(): int
{
return 4;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_DOC_COMMENT)) {
continue;
}

$doc = new DocBlock($token->getContent());
$annotations = $doc->getAnnotationsOfType('return');

if (0 === \count($annotations)) {
continue;
}

foreach ($annotations as $annotation) {
$this->fixAnnotation($annotation);
}

$newContent = $doc->getContent();

if ($newContent === $token->getContent()) {
continue;
}

if ('' === $newContent) {
$tokens->clearTokenAndMergeSurroundingWhitespace($index);

continue;
}

$tokens[$index] = new Token([T_DOC_COMMENT, $doc->getContent()]);
}
}




private function fixAnnotation(Annotation $annotation): void
{
$types = $annotation->getNormalizedTypes();

if (1 === \count($types) && ('null' === $types[0] || 'void' === $types[0])) {
$annotation->remove();
}
}
}
