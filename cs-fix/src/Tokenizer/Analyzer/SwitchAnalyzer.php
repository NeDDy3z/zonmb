<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\Analyzer\Analysis\SwitchAnalysis;
use PhpCsFixer\Tokenizer\Tokens;




final class SwitchAnalyzer
{

private static array $cache = [];

public static function belongsToSwitch(Tokens $tokens, int $index): bool
{
if (!$tokens[$index]->equals(':')) {
return false;
}

$tokensHash = md5(serialize($tokens->toArray()));

if (!\array_key_exists($tokensHash, self::$cache)) {
self::$cache[$tokensHash] = self::getColonIndicesForSwitch(clone $tokens);
}

return \in_array($index, self::$cache[$tokensHash], true);
}




private static function getColonIndicesForSwitch(Tokens $tokens): array
{
$colonIndices = [];


foreach (ControlCaseStructuresAnalyzer::findControlStructures($tokens, [T_SWITCH]) as $analysis) {
if ($tokens[$analysis->getOpenIndex()]->equals(':')) {
$colonIndices[] = $analysis->getOpenIndex();
}

foreach ($analysis->getCases() as $case) {
$colonIndices[] = $case->getColonIndex();
}

$defaultAnalysis = $analysis->getDefaultAnalysis();

if (null !== $defaultAnalysis) {
$colonIndices[] = $defaultAnalysis->getColonIndex();
}
}

return $colonIndices;
}
}
