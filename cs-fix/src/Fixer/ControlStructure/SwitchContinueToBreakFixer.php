<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class SwitchContinueToBreakFixer extends AbstractFixer
{



private array $switchLevels = [];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Switch case must not be ended with `continue` but with `break`.',
[
new CodeSample(
'<?php
switch ($foo) {
    case 1:
        continue;
}
'
),
new CodeSample(
'<?php
switch ($foo) {
    case 1:
        while($bar) {
            do {
                continue 3;
            } while(false);

            if ($foo + 1 > 3) {
                continue;
            }

            continue 2;
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

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound([T_SWITCH, T_CONTINUE]) && !$tokens->hasAlternativeSyntax();
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$count = \count($tokens);

for ($index = 1; $index < $count - 1; ++$index) {
$index = $this->doFix($tokens, $index, 0, false);
}
}




private function doFix(Tokens $tokens, int $index, int $depth, bool $isInSwitch): int
{
$token = $tokens[$index];

if ($token->isGivenKind([T_FOREACH, T_FOR, T_WHILE])) {

$index = $tokens->getNextTokenOfKind($index, ['(']);
$index = $tokens->getNextTokenOfKind($index, [')']);
$index = $tokens->getNextTokenOfKind($index, ['{', ';', [T_CLOSE_TAG]]);

if (!$tokens[$index]->equals('{')) {
return $index;
}

return $this->fixInLoop($tokens, $index, $depth + 1);
}

if ($token->isGivenKind(T_DO)) {
return $this->fixInLoop($tokens, $tokens->getNextTokenOfKind($index, ['{']), $depth + 1);
}

if ($token->isGivenKind(T_SWITCH)) {
return $this->fixInSwitch($tokens, $index, $depth + 1);
}

if ($token->isGivenKind(T_CONTINUE)) {
return $this->fixContinueWhenActsAsBreak($tokens, $index, $isInSwitch, $depth);
}

return $index;
}

private function fixInSwitch(Tokens $tokens, int $switchIndex, int $depth): int
{
$this->switchLevels[] = $depth;


$openIndex = $tokens->getNextTokenOfKind($switchIndex, ['{']);


$closeIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $openIndex);

for ($index = $openIndex + 1; $index < $closeIndex; ++$index) {
$index = $this->doFix($tokens, $index, $depth, true);
}

array_pop($this->switchLevels);

return $closeIndex;
}

private function fixInLoop(Tokens $tokens, int $openIndex, int $depth): int
{
$openCount = 1;

while (true) {
++$openIndex;
$token = $tokens[$openIndex];

if ($token->equals('{')) {
++$openCount;

continue;
}

if ($token->equals('}')) {
--$openCount;

if (0 === $openCount) {
break;
}

continue;
}

$openIndex = $this->doFix($tokens, $openIndex, $depth, false);
}

return $openIndex;
}

private function fixContinueWhenActsAsBreak(Tokens $tokens, int $continueIndex, bool $isInSwitch, int $depth): int
{
$followingContinueIndex = $tokens->getNextMeaningfulToken($continueIndex);
$followingContinueToken = $tokens[$followingContinueIndex];

if ($isInSwitch && $followingContinueToken->equals(';')) {
$this->replaceContinueWithBreakToken($tokens, $continueIndex); 

return $followingContinueIndex;
}

if (!$followingContinueToken->isGivenKind(T_LNUMBER)) {
return $followingContinueIndex;
}

$afterFollowingContinueIndex = $tokens->getNextMeaningfulToken($followingContinueIndex);

if (!$tokens[$afterFollowingContinueIndex]->equals(';')) {
return $afterFollowingContinueIndex; 
}



$jump = $followingContinueToken->getContent();
$jump = str_replace('_', '', $jump); 

if (\strlen($jump) > 2 && 'x' === $jump[1]) {
$jump = hexdec($jump); 
} elseif (\strlen($jump) > 2 && 'b' === $jump[1]) {
$jump = bindec($jump); 
} elseif (\strlen($jump) > 1 && '0' === $jump[0]) {
$jump = octdec($jump); 
} elseif (Preg::match('#^\d+$#', $jump)) { 
$jump = (float) $jump; 
} else {
return $afterFollowingContinueIndex; 
}

if ($jump > PHP_INT_MAX) {
return $afterFollowingContinueIndex; 
}

$jump = (int) $jump;

if ($isInSwitch && (1 === $jump || 0 === $jump)) {
$this->replaceContinueWithBreakToken($tokens, $continueIndex); 

return $afterFollowingContinueIndex;
}

$jumpDestination = $depth - $jump + 1;

if (\in_array($jumpDestination, $this->switchLevels, true)) {
$this->replaceContinueWithBreakToken($tokens, $continueIndex);

return $afterFollowingContinueIndex;
}

return $afterFollowingContinueIndex;
}

private function replaceContinueWithBreakToken(Tokens $tokens, int $index): void
{
$tokens[$index] = new Token([T_BREAK, 'break']);
}
}
