<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;






final class CompactNullableTypehintFixer extends AbstractProxyFixer implements DeprecatedFixerInterface
{
private CompactNullableTypeDeclarationFixer $compactNullableTypeDeclarationFixer;

public function __construct()
{
$this->compactNullableTypeDeclarationFixer = new CompactNullableTypeDeclarationFixer();

parent::__construct();
}

public function getDefinition(): FixerDefinitionInterface
{
$fixerDefinition = $this->compactNullableTypeDeclarationFixer->getDefinition();

return new FixerDefinition(
'Remove extra spaces in a nullable typehint.',
$fixerDefinition->getCodeSamples(),
$fixerDefinition->getDescription(),
$fixerDefinition->getRiskyDescription(),
);
}

public function getSuccessorsNames(): array
{
return [
$this->compactNullableTypeDeclarationFixer->getName(),
];
}

protected function createProxyFixers(): array
{
return [
$this->compactNullableTypeDeclarationFixer,
];
}
}
