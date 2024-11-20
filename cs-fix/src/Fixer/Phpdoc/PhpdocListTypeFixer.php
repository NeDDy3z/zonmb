<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractPhpdocTypesFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Tokens;

final class PhpdocListTypeFixer extends AbstractPhpdocTypesFixer
{
public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}

public function isRisky(): bool
{
return true;
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'PHPDoc `list` type must be used instead of `array` without a key.',
[
new CodeSample(<<<'PHP'
                    <?php
                    /**
                     * @param array<int> $x
                     * @param array<array<string>> $y
                     */

                    PHP),
],
null,
'Risky when `array` key should be present, but is missing.'
);
}







public function getPriority(): int
{
return 1;
}

protected function normalize(string $type): string
{
return Preg::replace('/array(?=<(?:[^,<]|<[^>]+>)+(>|{|\())/i', 'list', $type);
}
}
