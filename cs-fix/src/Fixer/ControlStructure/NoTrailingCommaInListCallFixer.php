<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;






final class NoTrailingCommaInListCallFixer extends AbstractProxyFixer implements DeprecatedFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Remove trailing commas in list function calls.',
[new CodeSample("<?php\nlist(\$a, \$b,) = foo();\n")]
);
}

public function getSuccessorsNames(): array
{
return array_keys($this->proxyFixers);
}

protected function createProxyFixers(): array
{
$fixer = new NoTrailingCommaInSinglelineFixer();
$fixer->configure(['elements' => ['array_destructuring']]);

return [$fixer];
}
}
