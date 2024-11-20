<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;




final class RangeAnalyzer
{
private function __construct()
{

}







public static function rangeEqualsRange(Tokens $tokens, array $range1, array $range2): bool
{
$leftStart = $range1['start'];
$leftEnd = $range1['end'];

if ($tokens[$leftStart]->isGivenKind([T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
$leftStart = $tokens->getNextMeaningfulToken($leftStart);
}

while ($tokens[$leftStart]->equals('(') && $tokens[$leftEnd]->equals(')')) {
$leftStart = $tokens->getNextMeaningfulToken($leftStart);
$leftEnd = $tokens->getPrevMeaningfulToken($leftEnd);
}

$rightStart = $range2['start'];
$rightEnd = $range2['end'];

if ($tokens[$rightStart]->isGivenKind([T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
$rightStart = $tokens->getNextMeaningfulToken($rightStart);
}

while ($tokens[$rightStart]->equals('(') && $tokens[$rightEnd]->equals(')')) {
$rightStart = $tokens->getNextMeaningfulToken($rightStart);
$rightEnd = $tokens->getPrevMeaningfulToken($rightEnd);
}

$arrayOpenTypes = ['[', [CT::T_ARRAY_INDEX_CURLY_BRACE_OPEN]];
$arrayCloseTypes = [']', [CT::T_ARRAY_INDEX_CURLY_BRACE_CLOSE]];

while (true) {
$leftToken = $tokens[$leftStart];
$rightToken = $tokens[$rightStart];

if (
!$leftToken->equals($rightToken)
&& !($leftToken->equalsAny($arrayOpenTypes) && $rightToken->equalsAny($arrayOpenTypes))
&& !($leftToken->equalsAny($arrayCloseTypes) && $rightToken->equalsAny($arrayCloseTypes))
) {
return false;
}

$leftStart = $tokens->getNextMeaningfulToken($leftStart);
$rightStart = $tokens->getNextMeaningfulToken($rightStart);

$reachedLeftEnd = null === $leftStart || $leftStart > $leftEnd; 
$reachedRightEnd = null === $rightStart || $rightStart > $rightEnd; 

if (!$reachedLeftEnd && !$reachedRightEnd) {
continue;
}

return $reachedLeftEnd && $reachedRightEnd;
}
}
}
