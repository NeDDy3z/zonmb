<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ReturnNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;




final class SimplifiedNullReturnFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'A return statement wishing to return `void` should not return `null`.',
[
new CodeSample("<?php return null;\n"),
new CodeSample(
<<<'EOT'
                        <?php
                        function foo() { return null; }
                        function bar(): int { return null; }
                        function baz(): ?int { return null; }
                        function xyz(): void { return null; }

                        EOT
),
]
);
}






public function getPriority(): int
{
return 16;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_RETURN);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_RETURN)) {
continue;
}

if ($this->needFixing($tokens, $index)) {
$this->clear($tokens, $index);
}
}
}




private function clear(Tokens $tokens, int $index): void
{
while (!$tokens[++$index]->equalsAny([';', [T_CLOSE_TAG]])) {
if ($this->shouldClearToken($tokens, $index)) {
$tokens->clearAt($index);
}
}
}




private function needFixing(Tokens $tokens, int $index): bool
{
if ($this->isStrictOrNullableReturnTypeFunction($tokens, $index)) {
return false;
}

$content = '';
while (!$tokens[$index]->equalsAny([';', [T_CLOSE_TAG]])) {
$index = $tokens->getNextMeaningfulToken($index);
$content .= $tokens[$index]->getContent();
}

$lastTokenContent = $tokens[$index]->getContent();
$content = substr($content, 0, -\strlen($lastTokenContent));

$content = ltrim($content, '(');
$content = rtrim($content, ')');

return 'null' === strtolower($content);
}






private function isStrictOrNullableReturnTypeFunction(Tokens $tokens, int $returnIndex): bool
{
$functionIndex = $returnIndex;
do {
$functionIndex = $tokens->getPrevTokenOfKind($functionIndex, [[T_FUNCTION]]);
if (null === $functionIndex) {
return false;
}
$openingCurlyBraceIndex = $tokens->getNextTokenOfKind($functionIndex, ['{']);
$closingCurlyBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $openingCurlyBraceIndex);
} while ($closingCurlyBraceIndex < $returnIndex);

$possibleVoidIndex = $tokens->getPrevMeaningfulToken($openingCurlyBraceIndex);
$isStrictReturnType = $tokens[$possibleVoidIndex]->isGivenKind([T_STRING, CT::T_ARRAY_TYPEHINT])
&& 'void' !== $tokens[$possibleVoidIndex]->getContent();

$nullableTypeIndex = $tokens->getNextTokenOfKind($functionIndex, [[CT::T_NULLABLE_TYPE]]);
$isNullableReturnType = null !== $nullableTypeIndex && $nullableTypeIndex < $openingCurlyBraceIndex;

return $isStrictReturnType || $isNullableReturnType;
}










private function shouldClearToken(Tokens $tokens, int $index): bool
{
$token = $tokens[$index];

if ($token->isComment()) {
return false;
}

if (!$token->isWhitespace()) {
return true;
}

if (
$tokens[$index + 1]->isComment()
|| $tokens[$index + 1]->equals([T_CLOSE_TAG])
|| ($tokens[$index - 1]->isComment() && $tokens[$index + 1]->equals(';'))
) {
return false;
}

return true;
}
}
