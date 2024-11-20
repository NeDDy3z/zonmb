<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Strict;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class DeclareStrictTypesFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Force strict types declaration in all files. Requires PHP >= 7.0.',
[
new CodeSample(
"<?php\n"
),
],
null,
'Forcing strict types will stop non strict code from working.'
);
}






public function getPriority(): int
{
return 2;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isMonolithicPhp() && !$tokens->isTokenKindFound(T_OPEN_TAG_WITH_ECHO);
}

public function isRisky(): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$openTagIndex = $tokens[0]->isGivenKind(T_INLINE_HTML) ? 1 : 0;

$sequenceLocation = $tokens->findSequence([[T_DECLARE, 'declare'], '(', [T_STRING, 'strict_types'], '=', [T_LNUMBER], ')'], $openTagIndex, null, false);
if (null === $sequenceLocation) {
$this->insertSequence($openTagIndex, $tokens); 

return;
}

$this->fixStrictTypesCasingAndValue($tokens, $sequenceLocation);
}




private function fixStrictTypesCasingAndValue(Tokens $tokens, array $sequence): void
{


foreach ($sequence as $index => $token) {
if ($token->isGivenKind(T_STRING)) {
$tokens[$index] = new Token([T_STRING, strtolower($token->getContent())]);

continue;
}
if ($token->isGivenKind(T_LNUMBER)) {
$tokens[$index] = new Token([T_LNUMBER, '1']);

break;
}
}
}

private function insertSequence(int $openTagIndex, Tokens $tokens): void
{
$sequence = [
new Token([T_DECLARE, 'declare']),
new Token('('),
new Token([T_STRING, 'strict_types']),
new Token('='),
new Token([T_LNUMBER, '1']),
new Token(')'),
new Token(';'),
];
$nextIndex = $openTagIndex + \count($sequence) + 1;

$tokens->insertAt($openTagIndex + 1, $sequence);


$content = $tokens[$openTagIndex]->getContent();
if (!str_contains($content, ' ') || str_contains($content, "\n")) {
$tokens[$openTagIndex] = new Token([$tokens[$openTagIndex]->getId(), trim($tokens[$openTagIndex]->getContent()).' ']);
}

if (\count($tokens) === $nextIndex) {
return; 
}

$lineEnding = $this->whitespacesConfig->getLineEnding();
if ($tokens[$nextIndex]->isWhitespace()) {
$content = $tokens[$nextIndex]->getContent();
$tokens[$nextIndex] = new Token([T_WHITESPACE, $lineEnding.ltrim($content, " \t")]);
} else {
$tokens->insertAt($nextIndex, new Token([T_WHITESPACE, $lineEnding]));
}
}
}
