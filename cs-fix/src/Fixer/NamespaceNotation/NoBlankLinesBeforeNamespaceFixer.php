<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\NamespaceNotation;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;






final class NoBlankLinesBeforeNamespaceFixer extends AbstractProxyFixer implements WhitespacesAwareFixerInterface, DeprecatedFixerInterface
{
public function getSuccessorsNames(): array
{
return array_keys($this->proxyFixers);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_NAMESPACE);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'There should be no blank lines before a namespace declaration.',
[
new CodeSample(
"<?php\n\n\n\nnamespace Example;\n"
),
]
);
}






public function getPriority(): int
{
return 0;
}

protected function createProxyFixers(): array
{
$blankLineBeforeNamespace = new BlankLinesBeforeNamespaceFixer();
$blankLineBeforeNamespace->configure([
'min_line_breaks' => 0,
'max_line_breaks' => 1,
]);

return [
$blankLineBeforeNamespace,
];
}
}
