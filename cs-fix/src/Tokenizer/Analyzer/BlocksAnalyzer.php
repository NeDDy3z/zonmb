<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;






final class BlocksAnalyzer
{
public function isBlock(Tokens $tokens, int $openIndex, int $closeIndex): bool
{
if (!$tokens->offsetExists($openIndex)) {
throw new \InvalidArgumentException(\sprintf('Tokex index %d for potential block opening does not exist.', $openIndex));
}

if (!$tokens->offsetExists($closeIndex)) {
throw new \InvalidArgumentException(\sprintf('Token index %d for potential block closure does not exist.', $closeIndex));
}

$blockType = $this->getBlockType($tokens[$openIndex]);

if (null === $blockType) {
return false;
}

return $closeIndex === $tokens->findBlockEnd($blockType, $openIndex);
}




private function getBlockType(Token $token): ?int
{
foreach (Tokens::getBlockEdgeDefinitions() as $blockType => $definition) {
if ($token->equals($definition['start'])) {
return $blockType;
}
}

return null;
}
}
