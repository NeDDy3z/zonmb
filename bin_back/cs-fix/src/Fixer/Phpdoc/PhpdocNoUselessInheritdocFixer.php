<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class PhpdocNoUselessInheritdocFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Classy that does not inherit must not have `@inheritdoc` tags.',
[
new CodeSample("<?php\n/** {@inheritdoc} */\nclass Sample\n{\n}\n"),
new CodeSample("<?php\nclass Sample\n{\n    /**\n     * @inheritdoc\n     */\n    public function Test()\n    {\n    }\n}\n"),
]
);
}







public function getPriority(): int
{
return 6;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT) && $tokens->isAnyTokenKindsFound([T_CLASS, T_INTERFACE]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{

for ($index = 1, $count = \count($tokens) - 4; $index < $count; ++$index) {
if ($tokens[$index]->isGivenKind([T_CLASS, T_INTERFACE])) {
$index = $this->fixClassy($tokens, $index);
}
}
}

private function fixClassy(Tokens $tokens, int $index): int
{

$classOpenIndex = $tokens->getNextTokenOfKind($index, ['{']);


$classEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $classOpenIndex);


$extendingOrImplementing = $this->isExtendingOrImplementing($tokens, $index, $classOpenIndex);

if (!$extendingOrImplementing) {

$this->fixClassyOutside($tokens, $index);
}


if (!$extendingOrImplementing && $this->isUsingTrait($tokens, $index, $classOpenIndex, $classEndIndex)) {
$extendingOrImplementing = true;
}

$this->fixClassyInside($tokens, $classOpenIndex, $classEndIndex, !$extendingOrImplementing);

return $classEndIndex;
}

private function fixClassyInside(Tokens $tokens, int $classOpenIndex, int $classEndIndex, bool $fixThisLevel): void
{
for ($i = $classOpenIndex; $i < $classEndIndex; ++$i) {
if ($tokens[$i]->isGivenKind(T_CLASS)) {
$i = $this->fixClassy($tokens, $i);
} elseif ($fixThisLevel && $tokens[$i]->isGivenKind(T_DOC_COMMENT)) {
$this->fixToken($tokens, $i);
}
}
}

private function fixClassyOutside(Tokens $tokens, int $classIndex): void
{
$previousIndex = $tokens->getPrevNonWhitespace($classIndex);
if ($tokens[$previousIndex]->isGivenKind(T_DOC_COMMENT)) {
$this->fixToken($tokens, $previousIndex);
}
}

private function fixToken(Tokens $tokens, int $tokenIndex): void
{
$count = 0;
$content = Preg::replaceCallback(
'#(\h*(?:@{*|{*\h*@)\h*inheritdoc\h*)([^}]*)((?:}*)\h*)#i',
static fn (array $matches): string => ' '.$matches[2],
$tokens[$tokenIndex]->getContent(),
-1,
$count
);

if ($count > 0) {
$tokens[$tokenIndex] = new Token([T_DOC_COMMENT, $content]);
}
}

private function isExtendingOrImplementing(Tokens $tokens, int $classIndex, int $classOpenIndex): bool
{
for ($index = $classIndex; $index < $classOpenIndex; ++$index) {
if ($tokens[$index]->isGivenKind([T_EXTENDS, T_IMPLEMENTS])) {
return true;
}
}

return false;
}

private function isUsingTrait(Tokens $tokens, int $classIndex, int $classOpenIndex, int $classCloseIndex): bool
{
if ($tokens[$classIndex]->isGivenKind(T_INTERFACE)) {

return false;
}

$useIndex = $tokens->getNextTokenOfKind($classOpenIndex, [[CT::T_USE_TRAIT]]);

return null !== $useIndex && $useIndex < $classCloseIndex;
}
}
