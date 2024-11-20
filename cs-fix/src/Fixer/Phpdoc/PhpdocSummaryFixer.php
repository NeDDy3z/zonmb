<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\ShortDescription;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class PhpdocSummaryFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'PHPDoc summary should end in either a full stop, exclamation mark, or question mark.',
[new CodeSample('<?php
/**
 * Foo function is great
 */
function foo () {}
')]
);
}







public function getPriority(): int
{
return 0;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_DOC_COMMENT)) {
continue;
}

$doc = new DocBlock($token->getContent());
$end = (new ShortDescription($doc))->getEnd();

if (null !== $end) {
$line = $doc->getLine($end);
$content = rtrim($line->getContent());

if (

!$this->isCorrectlyFormatted($content)

&& (1 === $end || ($doc->isMultiLine() && ':' !== substr(rtrim($doc->getLine(1)->getContent()), -1)))
) {
$line->setContent($content.'.'.$this->whitespacesConfig->getLineEnding());
$tokens[$index] = new Token([T_DOC_COMMENT, $doc->getContent()]);
}
}
}
}




private function isCorrectlyFormatted(string $content): bool
{
if (false !== stripos($content, '{@inheritdoc}')) {
return true;
}

return $content !== rtrim($content, '.:。!?¡¿！？');
}
}
