<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;






final class FunctionTypehintSpaceFixer extends AbstractProxyFixer implements DeprecatedFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Ensure single space between function\'s argument and its typehint.',
[
new CodeSample("<?php\nfunction sample(array\$a)\n{}\n"),
new CodeSample("<?php\nfunction sample(array  \$a)\n{}\n"),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound([T_FUNCTION, T_FN]);
}

public function getSuccessorsNames(): array
{
return array_keys($this->proxyFixers);
}

protected function createProxyFixers(): array
{
$fixer = new TypeDeclarationSpacesFixer();
$fixer->configure(['elements' => ['function']]);

return [$fixer];
}
}
