<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;





final class PhpdocNoAccessFixer extends AbstractProxyFixer
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'`@access` annotations should be omitted from PHPDoc.',
[
new CodeSample(
'<?php
class Foo
{
    /**
     * @internal
     * @access private
     */
    private $bar;
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
$fixer->configure(
['annotations' => ['access'],
'case_sensitive' => true,
]
);

return [$fixer];
}
}
