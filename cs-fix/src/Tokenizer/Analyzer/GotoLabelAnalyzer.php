<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\Tokens;




final class GotoLabelAnalyzer
{
public function belongsToGoToLabel(Tokens $tokens, int $index): bool
{
if (!$tokens[$index]->equals(':')) {
return false;
}

$prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($index);

if (!$tokens[$prevMeaningfulTokenIndex]->isGivenKind(T_STRING)) {
return false;
}

$prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($prevMeaningfulTokenIndex);

return $tokens[$prevMeaningfulTokenIndex]->equalsAny([':', ';', '{', '}', [T_OPEN_TAG]]);
}
}
