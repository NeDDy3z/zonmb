<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;




final class NoBlankLinesAfterPhpdocFixer extends AbstractFixer
{
public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'There should not be blank lines between docblock and the documented element.',
[
new CodeSample(
'<?php

/**
 * This is the bar class.
 */


class Bar {}
'
),
]
);
}







public function getPriority(): int
{
return -20;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
static $forbiddenSuccessors = [
T_BREAK,
T_COMMENT,
T_CONTINUE,
T_DECLARE,
T_DOC_COMMENT,
T_GOTO,
T_INCLUDE,
T_INCLUDE_ONCE,
T_NAMESPACE,
T_REQUIRE,
T_REQUIRE_ONCE,
T_RETURN,
T_THROW,
T_USE,
T_WHITESPACE,
];

foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_DOC_COMMENT)) {
continue;
}


$next = $tokens->getNextNonWhitespace($index);
if ($index + 2 === $next && false === $tokens[$next]->isGivenKind($forbiddenSuccessors)) {
$this->fixWhitespace($tokens, $index + 1);
}
}
}




private function fixWhitespace(Tokens $tokens, int $index): void
{
$content = $tokens[$index]->getContent();

if (substr_count($content, "\n") > 1) {

$tokens[$index] = new Token([T_WHITESPACE, substr($content, strrpos($content, "\n"))]);
}
}
}
