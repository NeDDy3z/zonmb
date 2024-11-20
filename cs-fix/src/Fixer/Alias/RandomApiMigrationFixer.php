<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Alias;

use PhpCsFixer\AbstractFunctionReferenceFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
@implements
@phpstan-type
@phpstan-type







*/
final class RandomApiMigrationFixer extends AbstractFunctionReferenceFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




private static array $argumentCounts = [
'getrandmax' => [0],
'mt_rand' => [1, 2],
'rand' => [0, 2],
'srand' => [0, 1],
'random_int' => [0, 2],
];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Replaces `rand`, `srand`, `getrandmax` functions calls with their `mt_*` analogs or `random_int`.',
[
new CodeSample("<?php\n\$a = getrandmax();\n\$a = rand(\$b, \$c);\n\$a = srand();\n"),
new CodeSample(
"<?php\n\$a = getrandmax();\n\$a = rand(\$b, \$c);\n\$a = srand();\n",
['replacements' => ['getrandmax' => 'mt_getrandmax']]
),
new CodeSample(
"<?php \$a = rand(\$b, \$c);\n",
['replacements' => ['rand' => 'random_int']]
),
],
null,
'Risky when the configured functions are overridden. Or when relying on the seed based generating of the numbers.'
);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$argumentsAnalyzer = new ArgumentsAnalyzer();

foreach ($this->configuration['replacements'] as $functionIdentity => $functionReplacement) {
if ($functionIdentity === $functionReplacement) {
continue;
}

$currIndex = 0;

do {

$boundaries = $this->find($functionIdentity, $tokens, $currIndex, $tokens->count() - 1);

if (null === $boundaries) {

continue 2;
}

[$functionName, $openParenthesis, $closeParenthesis] = $boundaries;
$count = $argumentsAnalyzer->countArguments($tokens, $openParenthesis, $closeParenthesis);

if (!\in_array($count, self::$argumentCounts[$functionIdentity], true)) {
continue 2;
}


$currIndex = $openParenthesis;
$tokens[$functionName] = new Token([T_STRING, $functionReplacement]);

if (0 === $count && 'random_int' === $functionReplacement) {
$tokens->insertAt($currIndex + 1, [
new Token([T_LNUMBER, '0']),
new Token(','),
new Token([T_WHITESPACE, ' ']),
new Token([T_STRING, 'getrandmax']),
new Token('('),
new Token(')'),
]);

$currIndex += 6;
}
} while (null !== $currIndex);
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('replacements', 'Mapping between replaced functions with the new ones.'))
->setAllowedTypes(['array<string, string>'])
->setAllowedValues([static function (array $value): bool {
foreach ($value as $functionName => $replacement) {
if (!\array_key_exists($functionName, self::$argumentCounts)) {
throw new InvalidOptionsException(\sprintf(
'Function "%s" is not handled by the fixer.',
$functionName
));
}
}

return true;
}])
->setDefault([
'getrandmax' => 'mt_getrandmax',
'rand' => 'mt_rand', 
'srand' => 'mt_srand',
])
->getOption(),
]);
}
}
