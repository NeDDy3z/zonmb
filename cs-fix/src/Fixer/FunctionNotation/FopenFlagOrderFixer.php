<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFopenFlagFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class FopenFlagOrderFixer extends AbstractFopenFlagFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Order the flags in `fopen` calls, `b` and `t` must be last.',
[new CodeSample("<?php\n\$a = fopen(\$foo, 'br+');\n")],
null,
'Risky when the function `fopen` is overridden.'
);
}

protected function fixFopenFlagToken(Tokens $tokens, int $argumentStartIndex, int $argumentEndIndex): void
{
$argumentFlagIndex = null;

for ($i = $argumentStartIndex; $i <= $argumentEndIndex; ++$i) {
if ($tokens[$i]->isGivenKind([T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
continue;
}

if (null !== $argumentFlagIndex) {
return; 
}

$argumentFlagIndex = $i;
}


if (null === $argumentFlagIndex || !$tokens[$argumentFlagIndex]->isGivenKind(T_CONSTANT_ENCAPSED_STRING)) {
return;
}

$content = $tokens[$argumentFlagIndex]->getContent();
$contentQuote = $content[0]; 

if ('b' === $contentQuote || 'B' === $contentQuote) {
$binPrefix = $contentQuote;
$contentQuote = $content[1]; 
$mode = substr($content, 2, -1);
} else {
$binPrefix = '';
$mode = substr($content, 1, -1);
}

$modeLength = \strlen($mode);
if ($modeLength < 2) {
return; 
}

if (false === $this->isValidModeString($mode)) {
return;
}

$split = $this->sortFlags(Preg::split('#([^\+]\+?)#', $mode, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE));
$newContent = $binPrefix.$contentQuote.implode('', $split).$contentQuote;

if ($content !== $newContent) {
$tokens[$argumentFlagIndex] = new Token([T_CONSTANT_ENCAPSED_STRING, $newContent]);
}
}






private function sortFlags(array $flags): array
{
usort(
$flags,
static function (string $flag1, string $flag2): int {
if ($flag1 === $flag2) {
return 0;
}

if ('b' === $flag1) {
return 1;
}

if ('b' === $flag2) {
return -1;
}

if ('t' === $flag1) {
return 1;
}

if ('t' === $flag2) {
return -1;
}

return $flag1 < $flag2 ? -1 : 1;
}
);

return $flags;
}
}
