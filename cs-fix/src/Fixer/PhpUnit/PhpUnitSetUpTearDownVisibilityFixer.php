<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\PhpUnit;

use PhpCsFixer\Fixer\AbstractPhpUnitFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;




final class PhpUnitSetUpTearDownVisibilityFixer extends AbstractPhpUnitFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Changes the visibility of the `setUp()` and `tearDown()` functions of PHPUnit to `protected`, to match the PHPUnit TestCase.',
[
new CodeSample(
'<?php
final class MyTest extends \PHPUnit_Framework_TestCase
{
    private $hello;
    public function setUp()
    {
        $this->hello = "hello";
    }

    public function tearDown()
    {
        $this->hello = null;
    }
}
'
),
],
null,
'This fixer may change functions named `setUp()` or `tearDown()` outside of PHPUnit tests, '.
'when a class is wrongly seen as a PHPUnit test.'
);
}

public function isRisky(): bool
{
return true;
}

protected function applyPhpUnitClassFix(Tokens $tokens, int $startIndex, int $endIndex): void
{
$counter = 0;
$tokensAnalyzer = new TokensAnalyzer($tokens);

for ($i = $endIndex - 1; $i > $startIndex; --$i) {
if (2 === $counter) {
break; 
}

if (!$this->isSetupOrTearDownMethod($tokens, $i)) {
continue;
}

++$counter;
$visibility = $tokensAnalyzer->getMethodAttributes($i)['visibility'];

if (T_PUBLIC === $visibility) {
$index = $tokens->getPrevTokenOfKind($i, [[T_PUBLIC]]);
$tokens[$index] = new Token([T_PROTECTED, 'protected']);

continue;
}

if (null === $visibility) {
$tokens->insertAt($i, [new Token([T_PROTECTED, 'protected']), new Token([T_WHITESPACE, ' '])]);
}
}
}

private function isSetupOrTearDownMethod(Tokens $tokens, int $index): bool
{
$tokensAnalyzer = new TokensAnalyzer($tokens);

$isMethod = $tokens[$index]->isGivenKind(T_FUNCTION) && !$tokensAnalyzer->isLambda($index);
if (!$isMethod) {
return false;
}

$functionNameIndex = $tokens->getNextMeaningfulToken($index);
$functionName = strtolower($tokens[$functionNameIndex]->getContent());

return 'setup' === $functionName || 'teardown' === $functionName;
}
}
