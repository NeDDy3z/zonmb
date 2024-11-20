<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class PhpdocVarAnnotationCorrectOrderFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'`@var` and `@type` annotations must have type and name in the correct order.',
[new CodeSample('<?php
/** @var $foo int */
$foo = 2 + 2;
')]
);
}







public function getPriority(): int
{
return 0;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_DOC_COMMENT)) {
continue;
}

if (false === stripos($token->getContent(), '@var') && false === stripos($token->getContent(), '@type')) {
continue;
}

$newContent = Preg::replace(
'/(@(?:type|var)\s*)(\$\S+)(\h+)([^\$](?:[^<\s]|<[^>]*>)*)(\s|\*)/i',
'$1$4$3$2$5',
$token->getContent()
);

if ($newContent === $token->getContent()) {
continue;
}

$tokens[$index] = new Token([$token->getId(), $newContent]);
}
}
}
