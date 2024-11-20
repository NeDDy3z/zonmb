<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Casing;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;




final class NativeFunctionTypeDeclarationCasingFixer extends AbstractProxyFixer implements DeprecatedFixerInterface
{
public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Native type declarations for functions should use the correct case.',
[
new CodeSample("<?php\nclass Bar {\n    public function Foo(CALLABLE \$bar)\n    {\n        return 1;\n    }\n}\n"),
new CodeSample(
"<?php\nfunction Foo(INT \$a): Bool\n{\n    return true;\n}\n"
),
new CodeSample(
"<?php\nfunction Foo(Iterable \$a): VOID\n{\n    echo 'Hello world';\n}\n"
),
new CodeSample(
"<?php\nfunction Foo(Object \$a)\n{\n    return 'hi!';\n}\n",
),
]
);
}

public function getSuccessorsNames(): array
{
return array_keys($this->proxyFixers);
}

protected function createProxyFixers(): array
{
$fixer = new NativeTypeDeclarationCasingFixer();

return [$fixer];
}
}
