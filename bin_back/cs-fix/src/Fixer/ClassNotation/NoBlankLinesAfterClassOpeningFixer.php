<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class NoBlankLinesAfterClassOpeningFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound(Token::getClassyTokenKinds());
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'There should be no empty lines after class opening brace.',
[
new CodeSample(
'<?php
final class Sample
{

    protected function foo()
    {
    }
}
'
),
]
);
}






public function getPriority(): int
{
return 0;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isClassy()) {
continue;
}

$startBraceIndex = $tokens->getNextTokenOfKind($index, ['{']);
if (!$tokens[$startBraceIndex + 1]->isWhitespace()) {
continue;
}

$this->fixWhitespace($tokens, $startBraceIndex + 1);
}
}




private function fixWhitespace(Tokens $tokens, int $index): void
{
$content = $tokens[$index]->getContent();

if (substr_count($content, "\n") > 1) {

$tokens[$index] = new Token([T_WHITESPACE, $this->whitespacesConfig->getLineEnding().substr($content, strrpos($content, "\n") + 1)]);
}
}
}
