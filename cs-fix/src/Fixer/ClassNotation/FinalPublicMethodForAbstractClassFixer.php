<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class FinalPublicMethodForAbstractClassFixer extends AbstractFixer
{



private array $magicMethods = [
'__construct' => true,
'__destruct' => true,
'__call' => true,
'__callstatic' => true,
'__get' => true,
'__set' => true,
'__isset' => true,
'__unset' => true,
'__sleep' => true,
'__wakeup' => true,
'__tostring' => true,
'__invoke' => true,
'__set_state' => true,
'__clone' => true,
'__debuginfo' => true,
];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'All `public` methods of `abstract` classes should be `final`.',
[
new CodeSample(
'<?php

abstract class AbstractMachine
{
    public function start()
    {}
}
'
),
],
'Enforce API encapsulation in an inheritance architecture. '
.'If you want to override a method, use the Template method pattern.',
'Risky when overriding `public` methods of `abstract` classes.'
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound([T_ABSTRACT, T_PUBLIC, T_FUNCTION]);
}

public function isRisky(): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$abstracts = array_keys($tokens->findGivenKind(T_ABSTRACT));

while ($abstractIndex = array_pop($abstracts)) {
$classIndex = $tokens->getNextTokenOfKind($abstractIndex, [[T_CLASS], [T_FUNCTION]]);
if (!$tokens[$classIndex]->isGivenKind(T_CLASS)) {
continue;
}

$classOpen = $tokens->getNextTokenOfKind($classIndex, ['{']);
$classClose = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $classOpen);

$this->fixClass($tokens, $classOpen, $classClose);
}
}

private function fixClass(Tokens $tokens, int $classOpenIndex, int $classCloseIndex): void
{
for ($index = $classCloseIndex - 1; $index > $classOpenIndex; --$index) {

if ($tokens[$index]->equals('}')) {
$index = $tokens->findBlockStart(Tokens::BLOCK_TYPE_CURLY_BRACE, $index);

continue;
}


if (!$tokens[$index]->isGivenKind(T_PUBLIC)) {
continue;
}

$nextIndex = $tokens->getNextMeaningfulToken($index);
$nextToken = $tokens[$nextIndex];

if ($nextToken->isGivenKind(T_STATIC)) {
$nextIndex = $tokens->getNextMeaningfulToken($nextIndex);
$nextToken = $tokens[$nextIndex];
}


if (!$nextToken->isGivenKind(T_FUNCTION)) {
continue;
}

$nextIndex = $tokens->getNextMeaningfulToken($nextIndex);
$nextToken = $tokens[$nextIndex];


if (isset($this->magicMethods[strtolower($nextToken->getContent())])) {
continue;
}

$prevIndex = $tokens->getPrevMeaningfulToken($index);
$prevToken = $tokens[$prevIndex];

if ($prevToken->isGivenKind(T_STATIC)) {
$index = $prevIndex;
$prevIndex = $tokens->getPrevMeaningfulToken($index);
$prevToken = $tokens[$prevIndex];
}


if ($prevToken->isGivenKind([T_ABSTRACT, T_FINAL])) {
$index = $prevIndex;

continue;
}

$tokens->insertAt(
$index,
[
new Token([T_FINAL, 'final']),
new Token([T_WHITESPACE, ' ']),
]
);
}
}
}
