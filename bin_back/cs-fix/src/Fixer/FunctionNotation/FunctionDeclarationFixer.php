<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
@implements
@phpstan-type
@phpstan-type













*/
final class FunctionDeclarationFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




public const SPACING_NONE = 'none';




public const SPACING_ONE = 'one';

private const SUPPORTED_SPACINGS = [self::SPACING_NONE, self::SPACING_ONE];

private string $singleLineWhitespaceOptions = " \t";

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound([T_FUNCTION, T_FN]);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Spaces should be properly placed in a function declaration.',
[
new CodeSample(
'<?php

class Foo
{
    public static function  bar   ( $baz , $foo )
    {
        return false;
    }
}

function  foo  ($bar, $baz)
{
    return false;
}
'
),
new CodeSample(
'<?php
$f = function () {};
',
['closure_function_spacing' => self::SPACING_NONE]
),
new CodeSample(
'<?php
$f = fn () => null;
',
['closure_fn_spacing' => self::SPACING_NONE]
),
]
);
}







public function getPriority(): int
{
return 31;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$tokensAnalyzer = new TokensAnalyzer($tokens);

for ($index = $tokens->count() - 1; $index >= 0; --$index) {
$token = $tokens[$index];

if (!$token->isGivenKind([T_FUNCTION, T_FN])) {
continue;
}

$startParenthesisIndex = $tokens->getNextTokenOfKind($index, ['(', ';', [T_CLOSE_TAG]]);

if (!$tokens[$startParenthesisIndex]->equals('(')) {
continue;
}

$endParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $startParenthesisIndex);

if (false === $this->configuration['trailing_comma_single_line']
&& !$tokens->isPartialCodeMultiline($index, $endParenthesisIndex)
) {
$commaIndex = $tokens->getPrevMeaningfulToken($endParenthesisIndex);

if ($tokens[$commaIndex]->equals(',')) {
$tokens->clearTokenAndMergeSurroundingWhitespace($commaIndex);
}
}

$startBraceIndex = $tokens->getNextTokenOfKind($endParenthesisIndex, [';', '{', [T_DOUBLE_ARROW]]);





if (
$tokens[$startBraceIndex]->equalsAny(['{', [T_DOUBLE_ARROW]])
&& (
!$tokens[$startBraceIndex - 1]->isWhitespace()
|| $tokens[$startBraceIndex - 1]->isWhitespace($this->singleLineWhitespaceOptions)
)
) {
$tokens->ensureWhitespaceAtIndex($startBraceIndex - 1, 1, ' ');
}

$afterParenthesisIndex = $tokens->getNextNonWhitespace($endParenthesisIndex);
$afterParenthesisToken = $tokens[$afterParenthesisIndex];

if ($afterParenthesisToken->isGivenKind(CT::T_USE_LAMBDA)) {

$tokens->ensureWhitespaceAtIndex($afterParenthesisIndex + 1, 0, ' ');

$useStartParenthesisIndex = $tokens->getNextTokenOfKind($afterParenthesisIndex, ['(']);
$useEndParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $useStartParenthesisIndex);

if (false === $this->configuration['trailing_comma_single_line']
&& !$tokens->isPartialCodeMultiline($index, $useEndParenthesisIndex)
) {
$commaIndex = $tokens->getPrevMeaningfulToken($useEndParenthesisIndex);

if ($tokens[$commaIndex]->equals(',')) {
$tokens->clearTokenAndMergeSurroundingWhitespace($commaIndex);
}
}


$this->fixParenthesisInnerEdge($tokens, $useStartParenthesisIndex, $useEndParenthesisIndex);


$tokens->ensureWhitespaceAtIndex($afterParenthesisIndex - 1, 1, ' ');
}


$this->fixParenthesisInnerEdge($tokens, $startParenthesisIndex, $endParenthesisIndex);
$isLambda = $tokensAnalyzer->isLambda($index);



if (!$isLambda && $tokens[$startParenthesisIndex - 1]->isWhitespace() && !$tokens[$tokens->getPrevNonWhitespace($startParenthesisIndex - 1)]->isComment()) {
$tokens->clearAt($startParenthesisIndex - 1);
}

$option = $token->isGivenKind(T_FN) ? 'closure_fn_spacing' : 'closure_function_spacing';

if ($isLambda && self::SPACING_NONE === $this->configuration[$option]) {


if ($tokens[$index + 1]->isWhitespace()) {
$tokens->clearAt($index + 1);
}
} else {


$tokens->ensureWhitespaceAtIndex($index + 1, 0, ' ');
}

if ($isLambda) {
$prev = $tokens->getPrevMeaningfulToken($index);

if ($tokens[$prev]->isGivenKind(T_STATIC)) {


$tokens->ensureWhitespaceAtIndex($prev + 1, 0, ' ');
}
}
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('closure_function_spacing', 'Spacing to use before open parenthesis for closures.'))
->setDefault(self::SPACING_ONE)
->setAllowedValues(self::SUPPORTED_SPACINGS)
->getOption(),
(new FixerOptionBuilder('closure_fn_spacing', 'Spacing to use before open parenthesis for short arrow functions.'))
->setDefault(self::SPACING_ONE) 
->setAllowedValues(self::SUPPORTED_SPACINGS)
->getOption(),
(new FixerOptionBuilder('trailing_comma_single_line', 'Whether trailing commas are allowed in single line signatures.'))
->setAllowedTypes(['bool'])
->setDefault(false)
->getOption(),
]);
}

private function fixParenthesisInnerEdge(Tokens $tokens, int $start, int $end): void
{
do {
--$end;
} while ($tokens->isEmptyAt($end));


if ($tokens[$end]->isWhitespace($this->singleLineWhitespaceOptions)) {
$tokens->clearAt($end);
}


if ($tokens[$start + 1]->isWhitespace($this->singleLineWhitespaceOptions)) {
$tokens->clearAt($start + 1);
}
}
}
