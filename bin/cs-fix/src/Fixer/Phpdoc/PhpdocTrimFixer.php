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




final class PhpdocTrimFixer extends AbstractFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'PHPDoc should start and end with content, excluding the very first and last line of the docblocks.',
[new CodeSample('<?php
/**
 *
 * Foo must be final class.
 *
 *
 */
final class Foo {}
')]
);
}







public function getPriority(): int
{
return -5;
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

$content = $token->getContent();
$content = $this->fixStart($content);


$content = $this->fixEnd($content);
$tokens[$index] = new Token([T_DOC_COMMENT, $content]);
}
}




private function fixStart(string $content): string
{
return Preg::replace(
'~
                (^/\*\*)            # DocComment begin
                (?:
                    \R\h*(?:\*\h*)? # lines without useful content
                    (?!\R\h*\*/)    # not followed by a DocComment end
                )+
                (\R\h*(?:\*\h*)?\S) # first line with useful content
            ~x',
'$1$2',
$content
);
}




private function fixEnd(string $content): string
{
return Preg::replace(
'~
                (\R\h*(?:\*\h*)?\S.*?) # last line with useful content
                (?:
                    (?<!/\*\*)         # not preceded by a DocComment start
                    \R\h*(?:\*\h*)?    # lines without useful content
                )+
                (\R\h*\*/$)            # DocComment end
            ~xu',
'$1$2',
$content
);
}
}
