<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\Fixer\AbstractShortOperatorFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

final class LongToShorthandOperatorFixer extends AbstractShortOperatorFixer
{



private static array $operators = [
'+' => [T_PLUS_EQUAL, '+='],
'-' => [T_MINUS_EQUAL, '-='],
'*' => [T_MUL_EQUAL, '*='],
'/' => [T_DIV_EQUAL, '/='],
'&' => [T_AND_EQUAL, '&='],
'.' => [T_CONCAT_EQUAL, '.='],
'%' => [T_MOD_EQUAL, '%='],
'|' => [T_OR_EQUAL, '|='],
'^' => [T_XOR_EQUAL, '^='],
];




private array $operatorTypes;

private TokensAnalyzer $tokensAnalyzer;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Shorthand notation for operators should be used if possible.',
[
new CodeSample("<?php\n\$i = \$i + 10;\n"),
],
null,
'Risky when applying for string offsets (e.g. `<?php $text = "foo"; $text[0] = $text[0] & "\x7F";`).',
);
}






public function getPriority(): int
{
return 17;
}

public function isRisky(): bool
{
return true;
}

public function isCandidate(Tokens $tokens): bool
{
if ($tokens->isAnyTokenKindsFound(array_keys(self::$operators))) {
return true;
}


return \defined('T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG');
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$this->operatorTypes = array_keys(self::$operators);
$this->tokensAnalyzer = new TokensAnalyzer($tokens);

parent::applyFix($file, $tokens);
}

protected function isOperatorTokenCandidate(Tokens $tokens, int $index): bool
{
if (!$tokens[$index]->equalsAny($this->operatorTypes)) {
return false;
}

while (null !== $index) {
$index = $tokens->getNextMeaningfulToken($index);
$otherToken = $tokens[$index];

if ($otherToken->equalsAny([';', [T_CLOSE_TAG]])) {
return true;
}


if ($otherToken->equals('?') || $otherToken->isGivenKind(T_INSTANCEOF)) {
return false;
}

$blockType = Tokens::detectBlockType($otherToken);

if (null !== $blockType) {
if (false === $blockType['isStart']) {
return true;
}

$index = $tokens->findBlockEnd($blockType['type'], $index);

continue;
}


if ($this->tokensAnalyzer->isBinaryOperator($index)) {
return false;
}
}

return false; 
}

protected function getReplacementToken(Token $token): Token
{
return new Token(self::$operators[$token->getContent()]);
}
}
