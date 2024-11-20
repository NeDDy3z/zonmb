<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tokenizer\Tokens;

abstract class AbstractIncrementOperatorFixer extends AbstractFixer
{
final protected function findStart(Tokens $tokens, int $index): int
{
do {
$index = $tokens->getPrevMeaningfulToken($index);
$token = $tokens[$index];

$blockType = Tokens::detectBlockType($token);
if (null !== $blockType && !$blockType['isStart']) {
$index = $tokens->findBlockStart($blockType['type'], $index);
$token = $tokens[$index];
}
} while (!$token->equalsAny(['$', [T_VARIABLE]]));

$prevIndex = $tokens->getPrevMeaningfulToken($index);
$prevToken = $tokens[$prevIndex];

if ($prevToken->equals('$')) {
return $this->findStart($tokens, $index);
}

if ($prevToken->isObjectOperator()) {
return $this->findStart($tokens, $prevIndex);
}

if ($prevToken->isGivenKind(T_PAAMAYIM_NEKUDOTAYIM)) {
$prevPrevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
if (!$tokens[$prevPrevIndex]->isGivenKind([T_STATIC, T_STRING])) {
return $this->findStart($tokens, $prevIndex);
}

$index = $tokens->getTokenNotOfKindsSibling($prevIndex, -1, [T_NS_SEPARATOR, T_STATIC, T_STRING]);
$index = $tokens->getNextMeaningfulToken($index);
}

return $index;
}
}
