<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractPhpdocTypesFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Tokens;

final class PhpdocArrayTypeFixer extends AbstractPhpdocTypesFixer
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
'PHPDoc `array<T>` type must be used instead of `T[]`.',
[
new CodeSample(<<<'PHP'
                    <?php
                    /**
                     * @param int[] $x
                     * @param string[][] $y
                     */

                    PHP),
],
null,
'Risky when using `T[]` in union types.'
);
}







public function getPriority(): int
{
return 2;
}

protected function normalize(string $type): string
{
if (Preg::match('/^\??\s*[\'"]/', $type)) {
return $type;
}

$prefix = '';
if (str_starts_with($type, '?')) {
$prefix = '?';
$type = substr($type, 1);
}

return $prefix.Preg::replaceCallback(
'/^(.+?)((?:\h*\[\h*\])+)$/',
static function (array $matches): string {
$type = $matches[1];
$level = substr_count($matches[2], '[');
if (str_starts_with($type, '(') && str_ends_with($type, ')')) {
$type = substr($type, 1, -1);
}

return str_repeat('array<', $level).$type.str_repeat('>', $level);
},
$type,
);
}
}
