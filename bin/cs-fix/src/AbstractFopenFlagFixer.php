<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;




abstract class AbstractFopenFlagFixer extends AbstractFunctionReferenceFixer
{
public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound([T_STRING, T_CONSTANT_ENCAPSED_STRING]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$argumentsAnalyzer = new ArgumentsAnalyzer();

$index = 0;
$end = $tokens->count() - 1;
while (true) {
$candidate = $this->find('fopen', $tokens, $index, $end);

if (null === $candidate) {
break;
}

$index = $candidate[1]; 


$arguments = $argumentsAnalyzer->getArguments(
$tokens,
$index,
$candidate[2]
);

$argumentsCount = \count($arguments); 

if ($argumentsCount < 2 || $argumentsCount > 4) {
continue;
}

$argumentStartIndex = array_keys($arguments)[1]; 

$this->fixFopenFlagToken(
$tokens,
$argumentStartIndex,
$arguments[$argumentStartIndex]
);
}
}

abstract protected function fixFopenFlagToken(Tokens $tokens, int $argumentStartIndex, int $argumentEndIndex): void;

protected function isValidModeString(string $mode): bool
{
$modeLength = \strlen($mode);
if ($modeLength < 1 || $modeLength > 13) { 
return false;
}

$validFlags = [
'a' => true,
'b' => true,
'c' => true,
'e' => true,
'r' => true,
't' => true,
'w' => true,
'x' => true,
];

if (!isset($validFlags[$mode[0]])) {
return false;
}

unset($validFlags[$mode[0]]);

for ($i = 1; $i < $modeLength; ++$i) {
if (isset($validFlags[$mode[$i]])) {
unset($validFlags[$mode[$i]]);

continue;
}

if ('+' !== $mode[$i]
|| (
'a' !== $mode[$i - 1] 
&& 'c' !== $mode[$i - 1]
&& 'r' !== $mode[$i - 1]
&& 'w' !== $mode[$i - 1]
&& 'x' !== $mode[$i - 1]
)
) {
return false;
}
}

return true;
}
}
