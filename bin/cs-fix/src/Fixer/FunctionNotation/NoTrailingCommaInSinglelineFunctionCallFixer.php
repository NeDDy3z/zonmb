<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;




final class NoTrailingCommaInSinglelineFunctionCallFixer extends AbstractProxyFixer implements DeprecatedFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'When making a method or function call on a single line there MUST NOT be a trailing comma after the last argument.',
[new CodeSample("<?php\nfoo(\$a,);\n")]
);
}






public function getPriority(): int
{
return 3;
}

public function getSuccessorsNames(): array
{
return array_keys($this->proxyFixers);
}

protected function createProxyFixers(): array
{
$fixer = new NoTrailingCommaInSinglelineFixer();
$fixer->configure(['elements' => ['arguments', 'array_destructuring']]);

return [$fixer];
}
}
