<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\PhpUnit;

use PhpCsFixer\Fixer\AbstractPhpUnitFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

/**
@implements
@phpstan-type
@phpstan-type







*/
final class PhpUnitSizeClassFixer extends AbstractPhpUnitFixer implements WhitespacesAwareFixerInterface, ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

private const SIZES = ['small', 'medium', 'large'];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'All PHPUnit test cases should have `@small`, `@medium` or `@large` annotation to enable run time limits.',
[
new CodeSample("<?php\nclass MyTest extends TestCase {}\n"),
new CodeSample("<?php\nclass MyTest extends TestCase {}\n", ['group' => 'medium']),
],
'The special groups [small, medium, large] provides a way to identify tests that are taking long to be executed.'
);
}






public function getPriority(): int
{
return 0;
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('group', 'Define a specific group to be used in case no group is already in use.'))
->setAllowedValues(self::SIZES)
->setDefault('small')
->getOption(),
]);
}

protected function applyPhpUnitClassFix(Tokens $tokens, int $startIndex, int $endIndex): void
{
$classIndex = $tokens->getPrevTokenOfKind($startIndex, [[T_CLASS]]);

if ($this->isAbstractClass($tokens, $classIndex)) {
return;
}

$this->ensureIsDocBlockWithAnnotation(
$tokens,
$classIndex,
$this->configuration['group'],
self::SIZES,
[
'phpunit\framework\attributes\small',
'phpunit\framework\attributes\medium',
'phpunit\framework\attributes\large',
],
);
}

private function isAbstractClass(Tokens $tokens, int $i): bool
{
$typeIndex = $tokens->getPrevMeaningfulToken($i);

return $tokens[$typeIndex]->isGivenKind(T_ABSTRACT);
}
}
