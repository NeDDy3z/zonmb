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






final class SingleBlankLineBeforeNamespaceFixer extends AbstractProxyFixer implements WhitespacesAwareFixerInterface, DeprecatedFixerInterface
{
public function getSuccessorsNames(): array
{
return array_keys($this->proxyFixers);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'There should be exactly one blank line before a namespace declaration.',
[
new CodeSample("<?php  namespace A {}\n"),
new CodeSample("<?php\n\n\nnamespace A{}\n"),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_NAMESPACE);
}






public function getPriority(): int
{
return parent::getPriority();
}

protected function createProxyFixers(): array
{
$blankLineBeforeNamespace = new BlankLinesBeforeNamespaceFixer();
$blankLineBeforeNamespace->configure([
'min_line_breaks' => 2,
'max_line_breaks' => 2,
]);

return [
$blankLineBeforeNamespace,
];
}
}
