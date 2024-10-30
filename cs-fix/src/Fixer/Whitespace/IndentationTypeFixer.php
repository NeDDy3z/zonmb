<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;






final class IndentationTypeFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{



private $indent;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Code MUST use configured indentation type.',
[
new CodeSample("<?php\n\nif (true) {\n\techo 'Hello!';\n}\n"),
]
);
}







public function getPriority(): int
{
return 50;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound([T_COMMENT, T_DOC_COMMENT, T_WHITESPACE]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$this->indent = $this->whitespacesConfig->getIndent();

foreach ($tokens as $index => $token) {
if ($token->isComment()) {
$tokens[$index] = $this->fixIndentInComment($tokens, $index);

continue;
}

if ($token->isWhitespace()) {
$tokens[$index] = $this->fixIndentToken($tokens, $index);

continue;
}
}
}

private function fixIndentInComment(Tokens $tokens, int $index): Token
{
$content = Preg::replace('/^(?:(?<! ) {1,3})?\t/m', '\1    ', $tokens[$index]->getContent(), -1, $count);


while (0 !== $count) {
$content = Preg::replace('/^(\ +)?\t/m', '\1    ', $content, -1, $count);
}

$indent = $this->indent;


$content = Preg::replaceCallback('/^(?:    )+/m', fn (array $matches): string => $this->getExpectedIndent($matches[0], $indent), $content);

return new Token([$tokens[$index]->getId(), $content]);
}

private function fixIndentToken(Tokens $tokens, int $index): Token
{
$content = $tokens[$index]->getContent();
$previousTokenHasTrailingLinebreak = false;


if (str_contains($tokens[$index - 1]->getContent(), "\n")) {
$content = "\n".$content;
$previousTokenHasTrailingLinebreak = true;
}

$indent = $this->indent;
$newContent = Preg::replaceCallback(
'/(\R)(\h+)/', 
function (array $matches) use ($indent): string {

$content = Preg::replace('/(?:(?<! ) {1,3})?\t/', '    ', $matches[2]);


return $matches[1].$this->getExpectedIndent($content, $indent);
},
$content
);

if ($previousTokenHasTrailingLinebreak) {
$newContent = substr($newContent, 1);
}

return new Token([T_WHITESPACE, $newContent]);
}




private function getExpectedIndent(string $content, string $indent): string
{
if ("\t" === $indent) {
$content = str_replace('    ', $indent, $content);
}

return $content;
}
}
