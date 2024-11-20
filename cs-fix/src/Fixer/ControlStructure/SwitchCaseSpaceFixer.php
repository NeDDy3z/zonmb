<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\SwitchAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\ControlCaseStructuresAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;






final class SwitchCaseSpaceFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Removes extra spaces between colon and case value.',
[
new CodeSample(
'<?php
    switch($a) {
        case 1   :
            break;
        default     :
            return 2;
    }
'
),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_SWITCH);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{

foreach (ControlCaseStructuresAnalyzer::findControlStructures($tokens, [T_SWITCH]) as $analysis) {
$default = $analysis->getDefaultAnalysis();

if (null !== $default) {
$index = $default->getIndex();

if (!$tokens[$index + 1]->isWhitespace() || !$tokens[$index + 2]->equalsAny([':', ';'])) {
continue;
}

$tokens->clearAt($index + 1);
}

foreach ($analysis->getCases() as $caseAnalysis) {
$colonIndex = $caseAnalysis->getColonIndex();
$valueIndex = $tokens->getPrevNonWhitespace($colonIndex);


if ($valueIndex === $colonIndex - 1 || $tokens[$valueIndex]->isComment()) {
continue;
}

$tokens->clearAt($valueIndex + 1);
}
}
}
}
