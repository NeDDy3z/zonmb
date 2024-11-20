<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\PhpUnit;

use PhpCsFixer\Fixer\AbstractPhpUnitFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;




final class PhpUnitTestClassRequiresCoversFixer extends AbstractPhpUnitFixer implements WhitespacesAwareFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Adds a default `@coversNothing` annotation to PHPUnit test classes that have no `@covers*` annotation.',
[
new CodeSample(
'<?php
final class MyTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeTest()
    {
        $this->assertSame(a(), b());
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

protected function applyPhpUnitClassFix(Tokens $tokens, int $startIndex, int $endIndex): void
{
$classIndex = $tokens->getPrevTokenOfKind($startIndex, [[T_CLASS]]);

$tokensAnalyzer = new TokensAnalyzer($tokens);
$modifiers = $tokensAnalyzer->getClassyModifiers($classIndex);

if (isset($modifiers['abstract'])) {
return; 
}

$this->ensureIsDocBlockWithAnnotation(
$tokens,
$classIndex,
'coversNothing',
[
'covers',
'coversDefaultClass',
'coversNothing',
],
[
'phpunit\framework\attributes\coversclass',
'phpunit\framework\attributes\coversnothing',
],
);
}
}
