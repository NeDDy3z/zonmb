<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Alias;

use PhpCsFixer\AbstractFunctionReferenceFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class MbStrFunctionsFixer extends AbstractFunctionReferenceFixer
{











private static array $functionsMap = [
'str_split' => ['alternativeName' => 'mb_str_split', 'argumentCount' => [1, 2, 3]],
'stripos' => ['alternativeName' => 'mb_stripos', 'argumentCount' => [2, 3]],
'stristr' => ['alternativeName' => 'mb_stristr', 'argumentCount' => [2, 3]],
'strlen' => ['alternativeName' => 'mb_strlen', 'argumentCount' => [1]],
'strpos' => ['alternativeName' => 'mb_strpos', 'argumentCount' => [2, 3]],
'strrchr' => ['alternativeName' => 'mb_strrchr', 'argumentCount' => [2]],
'strripos' => ['alternativeName' => 'mb_strripos', 'argumentCount' => [2, 3]],
'strrpos' => ['alternativeName' => 'mb_strrpos', 'argumentCount' => [2, 3]],
'strstr' => ['alternativeName' => 'mb_strstr', 'argumentCount' => [2, 3]],
'strtolower' => ['alternativeName' => 'mb_strtolower', 'argumentCount' => [1]],
'strtoupper' => ['alternativeName' => 'mb_strtoupper', 'argumentCount' => [1]],
'substr' => ['alternativeName' => 'mb_substr', 'argumentCount' => [2, 3]],
'substr_count' => ['alternativeName' => 'mb_substr_count', 'argumentCount' => [2, 3, 4]],
];










private array $functions;

public function __construct()
{
parent::__construct();

if (\PHP_VERSION_ID >= 8_03_00) {
self::$functionsMap['str_pad'] = ['alternativeName' => 'mb_str_pad', 'argumentCount' => [1, 2, 3, 4]];
}

if (\PHP_VERSION_ID >= 8_04_00) {
self::$functionsMap['trim'] = ['alternativeName' => 'mb_trim', 'argumentCount' => [1, 2]];
self::$functionsMap['ltrim'] = ['alternativeName' => 'mb_ltrim', 'argumentCount' => [1, 2]];
self::$functionsMap['rtrim'] = ['alternativeName' => 'mb_rtrim', 'argumentCount' => [1, 2]];
}

$this->functions = array_filter(
self::$functionsMap,
static fn (array $mapping): bool => (new \ReflectionFunction($mapping['alternativeName']))->isInternal()
);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Replace non multibyte-safe functions with corresponding mb function.',
[
new CodeSample(
'<?php
$a = strlen($a);
$a = strpos($a, $b);
$a = strrpos($a, $b);
$a = substr($a, $b);
$a = strtolower($a);
$a = strtoupper($a);
$a = stripos($a, $b);
$a = strripos($a, $b);
$a = strstr($a, $b);
$a = stristr($a, $b);
$a = strrchr($a, $b);
$a = substr_count($a, $b);
'
),
],
null,
'Risky when any of the functions are overridden, or when relying on the string byte size rather than its length in characters.'
);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$argumentsAnalyzer = new ArgumentsAnalyzer();
foreach ($this->functions as $functionIdentity => $functionReplacement) {
$currIndex = 0;
do {

$boundaries = $this->find($functionIdentity, $tokens, $currIndex, $tokens->count() - 1);
if (null === $boundaries) {

continue 2;
}

[$functionName, $openParenthesis, $closeParenthesis] = $boundaries;
$count = $argumentsAnalyzer->countArguments($tokens, $openParenthesis, $closeParenthesis);
if (!\in_array($count, $functionReplacement['argumentCount'], true)) {
continue 2;
}


$currIndex = $openParenthesis;

$tokens[$functionName] = new Token([T_STRING, $functionReplacement['alternativeName']]);
} while (null !== $currIndex);
}
}
}
