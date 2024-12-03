<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ArrayNotation;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;







final class NoTrailingCommaInSinglelineArrayFixer extends AbstractProxyFixer implements DeprecatedFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'PHP single-line arrays should not have trailing comma.',
[new CodeSample("<?php\n\$a = array('sample',  );\n")]
);
}

public function getSuccessorsNames(): array
{
return array_keys($this->proxyFixers);
}

protected function createProxyFixers(): array
{
$fixer = new NoTrailingCommaInSinglelineFixer();
$fixer->configure(['elements' => ['array']]);

return [$fixer];
}
}
