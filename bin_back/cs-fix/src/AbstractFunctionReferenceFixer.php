<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;






abstract class AbstractFunctionReferenceFixer extends AbstractFixer
{



private $functionsAnalyzer;

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_STRING);
}

public function isRisky(): bool
{
return true;
}







protected function find(string $functionNameToSearch, Tokens $tokens, int $start = 0, ?int $end = null): ?array
{
if (null === $this->functionsAnalyzer) {
$this->functionsAnalyzer = new FunctionsAnalyzer();
}


$end ??= $tokens->count();


$candidateSequence = [[T_STRING, $functionNameToSearch], '('];
$matches = $tokens->findSequence($candidateSequence, $start, $end, false);

if (null === $matches) {
return null; 
}


[$functionName, $openParenthesis] = array_keys($matches);

if (!$this->functionsAnalyzer->isGlobalFunctionCall($tokens, $functionName)) {
return $this->find($functionNameToSearch, $tokens, $openParenthesis, $end);
}

return [$functionName, $openParenthesis, $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesis)];
}
}
