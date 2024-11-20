<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\PhpTag;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;






final class FullOpeningTagFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'PHP code must use the long `<?php` tags or short-echo `<?=` tags and not other tag variations.',
[
new CodeSample(
'<?

echo "Hello!";
'
),
]
);
}

public function getPriority(): int
{

return 98;
}

public function isCandidate(Tokens $tokens): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$content = $tokens->generateCode();


$newContent = Preg::replace('/<\?(?:phP|pHp|pHP|Php|PhP|PHp|PHP)?(\s|$)/', '<?php$1', $content, -1, $count);

if (0 === $count) {
return;
}






$newTokens = Tokens::fromCode($newContent);

$tokensOldContentLength = 0;

foreach ($newTokens as $index => $token) {
if ($token->isGivenKind(T_OPEN_TAG)) {
$tokenContent = $token->getContent();
$possibleOpenContent = substr($content, $tokensOldContentLength, 5);

if (false === $possibleOpenContent || '<?php' !== strtolower($possibleOpenContent)) { /**
@phpstan-ignore-line */
$tokenContent = '<? ';
}

$tokensOldContentLength += \strlen($tokenContent);

continue;
}

if ($token->isGivenKind([T_COMMENT, T_DOC_COMMENT, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE, T_STRING])) {
$tokenContent = '';
$tokenContentLength = 0;
$parts = explode('<?php', $token->getContent());
$iLast = \count($parts) - 1;

foreach ($parts as $i => $part) {
$tokenContent .= $part;
$tokenContentLength += \strlen($part);

if ($i !== $iLast) {
$originalTokenContent = substr($content, $tokensOldContentLength + $tokenContentLength, 5);
if ('<?php' === strtolower($originalTokenContent)) {
$tokenContent .= $originalTokenContent;
$tokenContentLength += 5;
} else {
$tokenContent .= '<?';
$tokenContentLength += 2;
}
}
}

$newTokens[$index] = new Token([$token->getId(), $tokenContent]);
$token = $newTokens[$index];
}

$tokensOldContentLength += \strlen($token->getContent());
}

$tokens->overrideRange(0, $tokens->count() - 1, $newTokens);
}
}
