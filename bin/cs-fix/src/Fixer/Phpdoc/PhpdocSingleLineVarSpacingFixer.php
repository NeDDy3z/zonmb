<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class PhpdocSingleLineVarSpacingFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Single line `@var` PHPDoc should have proper spacing.',
[new CodeSample("<?php /**@var   MyClass   \$a   */\n\$a = test();\n")]
);
}







public function getPriority(): int
{
return -10;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound([T_COMMENT, T_DOC_COMMENT]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{

foreach ($tokens as $index => $token) {
if (!$token->isComment()) {
continue;
}

$content = $token->getContent();
$fixedContent = $this->fixTokenContent($content);

if ($content !== $fixedContent) {
$tokens[$index] = new Token([T_DOC_COMMENT, $fixedContent]);
}
}
}

private function fixTokenContent(string $content): string
{
return Preg::replaceCallback(
'#^/\*\*\h*@var\h+(\S+)\h*(\$\S+)?\h*([^\n]*)\*/$#',
static function (array $matches) {
$content = '/** @var';

for ($i = 1, $m = \count($matches); $i < $m; ++$i) {
if ('' !== $matches[$i]) {
$content .= ' '.$matches[$i];
}
}

return rtrim($content).' */';
},
$content
);
}
}
