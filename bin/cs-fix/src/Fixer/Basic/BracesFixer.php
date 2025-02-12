<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Basic;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureContinuationPositionFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareParenthesesFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAroundConstructFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;

/**
@implements
@phpstan-type
@phpstan-type



















*/
final class BracesFixer extends AbstractProxyFixer implements ConfigurableFixerInterface, WhitespacesAwareFixerInterface, DeprecatedFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




public const LINE_NEXT = 'next';




public const LINE_SAME = 'same';




private $bracesPositionFixer;




private $controlStructureContinuationPositionFixer;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'The body of each structure MUST be enclosed by braces. Braces should be properly placed. Body of braces should be properly indented.',
[
new CodeSample(
'<?php

class Foo {
    public function bar($baz) {
        if ($baz = 900) echo "Hello!";

        if ($baz = 9000)
            echo "Wait!";

        if ($baz == true)
        {
            echo "Why?";
        }
        else
        {
            echo "Ha?";
        }

        if (is_array($baz))
            foreach ($baz as $b)
            {
                echo $b;
            }
    }
}
'
),
new CodeSample(
'<?php
$positive = function ($item) { return $item >= 0; };
$negative = function ($item) {
                return $item < 0; };
',
['allow_single_line_closure' => true]
),
new CodeSample(
'<?php

class Foo
{
    public function bar($baz)
    {
        if ($baz = 900) echo "Hello!";

        if ($baz = 9000)
            echo "Wait!";

        if ($baz == true)
        {
            echo "Why?";
        }
        else
        {
            echo "Ha?";
        }

        if (is_array($baz))
            foreach ($baz as $b)
            {
                echo $b;
            }
    }
}
',
['position_after_functions_and_oop_constructs' => self::LINE_SAME]
),
]
);
}







public function getPriority(): int
{
return 35;
}

public function getSuccessorsNames(): array
{
return array_keys($this->proxyFixers);
}

protected function configurePostNormalisation(): void
{
$this->getBracesPositionFixer()->configure([
'control_structures_opening_brace' => $this->translatePositionOption($this->configuration['position_after_control_structures']),
'functions_opening_brace' => $this->translatePositionOption($this->configuration['position_after_functions_and_oop_constructs']),
'anonymous_functions_opening_brace' => $this->translatePositionOption($this->configuration['position_after_anonymous_constructs']),
'classes_opening_brace' => $this->translatePositionOption($this->configuration['position_after_functions_and_oop_constructs']),
'anonymous_classes_opening_brace' => $this->translatePositionOption($this->configuration['position_after_anonymous_constructs']),
'allow_single_line_empty_anonymous_classes' => $this->configuration['allow_single_line_anonymous_class_with_empty_body'],
'allow_single_line_anonymous_functions' => $this->configuration['allow_single_line_closure'],
]);

$this->getControlStructureContinuationPositionFixer()->configure([
'position' => self::LINE_NEXT === $this->configuration['position_after_control_structures']
? ControlStructureContinuationPositionFixer::NEXT_LINE
: ControlStructureContinuationPositionFixer::SAME_LINE,
]);
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('allow_single_line_anonymous_class_with_empty_body', 'Whether single line anonymous class with empty body notation should be allowed.'))
->setAllowedTypes(['bool'])
->setDefault(false)
->getOption(),
(new FixerOptionBuilder('allow_single_line_closure', 'Whether single line lambda notation should be allowed.'))
->setAllowedTypes(['bool'])
->setDefault(false)
->getOption(),
(new FixerOptionBuilder('position_after_functions_and_oop_constructs', 'Whether the opening brace should be placed on "next" or "same" line after classy constructs (non-anonymous classes, interfaces, traits, methods and non-lambda functions).'))
->setAllowedValues([self::LINE_NEXT, self::LINE_SAME])
->setDefault(self::LINE_NEXT)
->getOption(),
(new FixerOptionBuilder('position_after_control_structures', 'Whether the opening brace should be placed on "next" or "same" line after control structures.'))
->setAllowedValues([self::LINE_NEXT, self::LINE_SAME])
->setDefault(self::LINE_SAME)
->getOption(),
(new FixerOptionBuilder('position_after_anonymous_constructs', 'Whether the opening brace should be placed on "next" or "same" line after anonymous constructs (anonymous classes and lambda functions).'))
->setAllowedValues([self::LINE_NEXT, self::LINE_SAME])
->setDefault(self::LINE_SAME)
->getOption(),
]);
}

protected function createProxyFixers(): array
{
$singleSpaceAroundConstructFixer = new SingleSpaceAroundConstructFixer();
$singleSpaceAroundConstructFixer->configure([
'constructs_contain_a_single_space' => [],
'constructs_followed_by_a_single_space' => ['elseif', 'for', 'foreach', 'if', 'match', 'while', 'use_lambda'],
'constructs_preceded_by_a_single_space' => ['use_lambda'],
]);

$noExtraBlankLinesFixer = new NoExtraBlankLinesFixer();
$noExtraBlankLinesFixer->configure([
'tokens' => ['curly_brace_block'],
]);

return [
$singleSpaceAroundConstructFixer,
new ControlStructureBracesFixer(),
$noExtraBlankLinesFixer,
$this->getBracesPositionFixer(),
$this->getControlStructureContinuationPositionFixer(),
new DeclareParenthesesFixer(),
new NoMultipleStatementsPerLineFixer(),
new StatementIndentationFixer(true),
];
}

private function getBracesPositionFixer(): BracesPositionFixer
{
if (null === $this->bracesPositionFixer) {
$this->bracesPositionFixer = new BracesPositionFixer();
}

return $this->bracesPositionFixer;
}

private function getControlStructureContinuationPositionFixer(): ControlStructureContinuationPositionFixer
{
if (null === $this->controlStructureContinuationPositionFixer) {
$this->controlStructureContinuationPositionFixer = new ControlStructureContinuationPositionFixer();
}

return $this->controlStructureContinuationPositionFixer;
}




private function translatePositionOption(string $option): string
{
return self::LINE_NEXT === $option
? BracesPositionFixer::NEXT_LINE_UNLESS_NEWLINE_AT_SIGNATURE_END
: BracesPositionFixer::SAME_LINE;
}
}
