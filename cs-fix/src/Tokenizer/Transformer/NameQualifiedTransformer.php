<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\Processor\ImportProcessor;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;






final class NameQualifiedTransformer extends AbstractTransformer
{
public function getPriority(): int
{
return 1; 
}

public function getRequiredPhpVersionId(): int
{
return 8_00_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if ($token->isGivenKind([T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED])) {
$this->transformQualified($tokens, $token, $index);
} elseif ($token->isGivenKind(T_NAME_RELATIVE)) {
$this->transformRelative($tokens, $token, $index);
}
}

public function getCustomTokens(): array
{
return [];
}

private function transformQualified(Tokens $tokens, Token $token, int $index): void
{
$newTokens = ImportProcessor::tokenizeName($token->getContent());

$tokens->overrideRange($index, $index, $newTokens);
}

private function transformRelative(Tokens $tokens, Token $token, int $index): void
{
$newTokens = ImportProcessor::tokenizeName($token->getContent());
$newTokens[0] = new Token([T_NAMESPACE, 'namespace']);

$tokens->overrideRange($index, $index, $newTokens);
}
}
