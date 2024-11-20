<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Alias;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\PregException;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class EregToPregFixer extends AbstractFixer
{




private static array $functions = [
['ereg', 'preg_match', ''],
['eregi', 'preg_match', 'i'],
['ereg_replace', 'preg_replace', ''],
['eregi_replace', 'preg_replace', 'i'],
['split', 'preg_split', ''],
['spliti', 'preg_split', 'i'],
];




private static array $delimiters = ['/', '#', '!'];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Replace deprecated `ereg` regular expression functions with `preg`.',
[new CodeSample("<?php \$x = ereg('[A-Z]');\n")],
null,
'Risky if the `ereg` function is overridden.'
);
}






public function getPriority(): int
{
return 0;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_STRING);
}

public function isRisky(): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$end = $tokens->count() - 1;
$functionsAnalyzer = new FunctionsAnalyzer();

foreach (self::$functions as $map) {

$seq = [[T_STRING, $map[0]], '(', [T_CONSTANT_ENCAPSED_STRING]];
$currIndex = 0;

while (true) {
$match = $tokens->findSequence($seq, $currIndex, $end, false);


if (null === $match) {
break;
}





$match = array_keys($match);


$currIndex = $match[2];

if (!$functionsAnalyzer->isGlobalFunctionCall($tokens, $match[0])) {
continue;
}


$next = $tokens->getNextMeaningfulToken($match[2]);

if (null === $next || !$tokens[$next]->equalsAny([',', ')'])) {
continue;
}


$regexTokenContent = $tokens[$match[2]]->getContent();

if ('b' === $regexTokenContent[0] || 'B' === $regexTokenContent[0]) {
$quote = $regexTokenContent[1];
$prefix = $regexTokenContent[0];
$string = substr($regexTokenContent, 2, -1);
} else {
$quote = $regexTokenContent[0];
$prefix = '';
$string = substr($regexTokenContent, 1, -1);
}

$delim = $this->getBestDelimiter($string);
$preg = $delim.addcslashes($string, $delim).$delim.'D'.$map[2];


if (!$this->checkPreg($preg)) {
continue;
}


$tokens[$match[0]] = new Token([T_STRING, $map[1]]);
$tokens[$match[2]] = new Token([T_CONSTANT_ENCAPSED_STRING, $prefix.$quote.$preg.$quote]);
}
}
}






private function checkPreg(string $pattern): bool
{
try {
Preg::match($pattern, '');

return true;
} catch (PregException $e) {
return false;
}
}








private function getBestDelimiter(string $pattern): string
{

$delimiters = [];

foreach (self::$delimiters as $k => $d) {
if (!str_contains($pattern, $d)) {
return $d;
}

$delimiters[$d] = [substr_count($pattern, $d), $k];
}


uasort($delimiters, static function (array $a, array $b): int {
if ($a[0] === $b[0]) {
return $a[1] <=> $b[1];
}

return $a[0] <=> $b[0];
});

return array_key_first($delimiters);
}
}
