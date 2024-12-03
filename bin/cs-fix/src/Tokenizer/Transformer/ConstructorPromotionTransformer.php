<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;








final class ConstructorPromotionTransformer extends AbstractTransformer
{
public function getRequiredPhpVersionId(): int
{
return 8_00_00;
}

public function process(Tokens $tokens, Token $token, int $index): void
{
if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
return;
}

$functionNameIndex = $tokens->getNextMeaningfulToken($index);

if (!$tokens[$functionNameIndex]->isGivenKind(T_STRING) || '__construct' !== strtolower($tokens[$functionNameIndex]->getContent())) {
return;
}


$openParenthesisIndex = $tokens->getNextMeaningfulToken($functionNameIndex); 
$closeParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesisIndex);

for ($argsIndex = $openParenthesisIndex; $argsIndex < $closeParenthesisIndex; ++$argsIndex) {
if ($tokens[$argsIndex]->isGivenKind(T_PUBLIC)) {
$tokens[$argsIndex] = new Token([CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC, $tokens[$argsIndex]->getContent()]);
} elseif ($tokens[$argsIndex]->isGivenKind(T_PROTECTED)) {
$tokens[$argsIndex] = new Token([CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED, $tokens[$argsIndex]->getContent()]);
} elseif ($tokens[$argsIndex]->isGivenKind(T_PRIVATE)) {
$tokens[$argsIndex] = new Token([CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE, $tokens[$argsIndex]->getContent()]);
}
}
}

public function getCustomTokens(): array
{
return [
CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC,
CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED,
CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE,
];
}
}
