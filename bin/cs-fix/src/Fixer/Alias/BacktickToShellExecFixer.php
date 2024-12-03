<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Alias;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class BacktickToShellExecFixer extends AbstractFixer
{
public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound('`');
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Converts backtick operators to `shell_exec` calls.',
[
new CodeSample(
<<<'EOT'
                        <?php
                        $plain = `ls -lah`;
                        $withVar = `ls -lah $var1 ${var2} {$var3} {$var4[0]} {$var5->call()}`;

                        EOT
),
],
'Conversion is done only when it is non risky, so when special chars like single-quotes, double-quotes and backticks are not used inside the command.'
);
}






public function getPriority(): int
{
return 17;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$backtickStarted = false;
$backtickTokens = [];
for ($index = $tokens->count() - 1; $index > 0; --$index) {
$token = $tokens[$index];

if (!$token->equals('`')) {
if ($backtickStarted) {
$backtickTokens[$index] = $token;
}

continue;
}

$backtickTokens[$index] = $token;

if ($backtickStarted) {
$this->fixBackticks($tokens, $backtickTokens);
$backtickTokens = [];
}

$backtickStarted = !$backtickStarted;
}
}






private function fixBackticks(Tokens $tokens, array $backtickTokens): void
{

ksort($backtickTokens);
$openingBacktickIndex = array_key_first($backtickTokens);
$closingBacktickIndex = array_key_last($backtickTokens);


array_shift($backtickTokens);
array_pop($backtickTokens);



$count = \count($backtickTokens);

$newTokens = [
new Token([T_STRING, 'shell_exec']),
new Token('('),
];

if (1 !== $count) {
$newTokens[] = new Token('"');
}

foreach ($backtickTokens as $token) {
if (!$token->isGivenKind(T_ENCAPSED_AND_WHITESPACE)) {
$newTokens[] = $token;

continue;
}

$content = $token->getContent();

if (Preg::match('/[`"\']/u', $content)) {
return;
}

$kind = T_ENCAPSED_AND_WHITESPACE;

if (1 === $count) {
$content = '"'.$content.'"';
$kind = T_CONSTANT_ENCAPSED_STRING;
}

$newTokens[] = new Token([$kind, $content]);
}

if (1 !== $count) {
$newTokens[] = new Token('"');
}

$newTokens[] = new Token(')');

$tokens->overrideRange($openingBacktickIndex, $closingBacktickIndex, $newTokens);
}
}