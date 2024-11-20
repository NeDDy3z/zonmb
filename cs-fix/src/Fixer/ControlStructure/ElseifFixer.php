<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;






final class ElseifFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'The keyword `elseif` should be used instead of `else if` so that all control keywords look like single words.',
[new CodeSample("<?php\nif (\$a) {\n} else if (\$b) {\n}\n")]
);
}






public function getPriority(): int
{
return 40;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAllTokenKindsFound([T_IF, T_ELSE]);
}






protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_ELSE)) {
continue;
}

$ifTokenIndex = $tokens->getNextMeaningfulToken($index);


if (!$tokens[$ifTokenIndex]->isGivenKind(T_IF)) {
continue;
}


$conditionEndBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $tokens->getNextMeaningfulToken($ifTokenIndex));
$afterConditionIndex = $tokens->getNextMeaningfulToken($conditionEndBraceIndex);
if ($tokens[$afterConditionIndex]->equals(':')) {
continue;
}



$tokens->clearAt($index + 1);


$tokens[$index] = new Token([T_ELSEIF, 'elseif']);


$tokens->clearAt($ifTokenIndex);

$beforeIfTokenIndex = $tokens->getPrevNonWhitespace($ifTokenIndex);


if ($tokens[$beforeIfTokenIndex]->isComment() && $tokens[$ifTokenIndex + 1]->isWhitespace()) {
$tokens->clearAt($ifTokenIndex + 1);
}
}
}
}
