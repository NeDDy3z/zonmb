<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;





final class PhpdocNoPackageFixer extends AbstractProxyFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'`@package` and `@subpackage` annotations should be omitted from PHPDoc.',
[
new CodeSample(
'<?php
/**
 * @internal
 * @package Foo
 * subpackage Bar
 */
class Baz
{
}
'
),
]
);
}







public function getPriority(): int
{
return parent::getPriority();
}

protected function createProxyFixers(): array
{
$fixer = new GeneralPhpdocAnnotationRemoveFixer();
$fixer->configure([
'annotations' => ['package', 'subpackage'],
'case_sensitive' => true,
]);

return [$fixer];
}
}
