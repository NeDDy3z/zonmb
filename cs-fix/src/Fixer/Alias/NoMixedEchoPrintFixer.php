<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Alias;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
@implements
@phpstan-type
@phpstan-type







*/
final class NoMixedEchoPrintFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;




private int $candidateTokenType;

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'Either language construct `print` or `echo` should be used.',
[
new CodeSample("<?php print 'example';\n"),
new CodeSample("<?php echo('example');\n", ['use' => 'print']),
]
);
}






public function getPriority(): int
{
return -10;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound($this->candidateTokenType);
}

protected function configurePostNormalisation(): void
{
$this->candidateTokenType = 'echo' === $this->configuration['use'] ? T_PRINT : T_ECHO;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if ($token->isGivenKind($this->candidateTokenType)) {
if (T_PRINT === $this->candidateTokenType) {
$this->fixPrintToEcho($tokens, $index);
} else {
$this->fixEchoToPrint($tokens, $index);
}
}
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('use', 'The desired language construct.'))
->setAllowedValues(['print', 'echo'])
->setDefault('echo')
->getOption(),
]);
}

private function fixEchoToPrint(Tokens $tokens, int $index): void
{
$nextTokenIndex = $tokens->getNextMeaningfulToken($index);
$endTokenIndex = $tokens->getNextTokenOfKind($index, [';', [T_CLOSE_TAG]]);
$canBeConverted = true;

for ($i = $nextTokenIndex; $i < $endTokenIndex; ++$i) {
if ($tokens[$i]->equalsAny(['(', [CT::T_ARRAY_SQUARE_BRACE_OPEN]])) {
$blockType = Tokens::detectBlockType($tokens[$i]);
$i = $tokens->findBlockEnd($blockType['type'], $i);
}

if ($tokens[$i]->equals(',')) {
$canBeConverted = false;

break;
}
}

if (false === $canBeConverted) {
return;
}

$tokens[$index] = new Token([T_PRINT, 'print']);
}

private function fixPrintToEcho(Tokens $tokens, int $index): void
{
$prevToken = $tokens[$tokens->getPrevMeaningfulToken($index)];

if (!$prevToken->equalsAny([';', '{', '}', ')', [T_OPEN_TAG], [T_ELSE]])) {
return;
}

$tokens[$index] = new Token([T_ECHO, 'echo']);
}
}
