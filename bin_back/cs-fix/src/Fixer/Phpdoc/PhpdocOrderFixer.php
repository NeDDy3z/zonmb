<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
@implements
@phpstan-type
@phpstan-type








*/
final class PhpdocOrderFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;

/**
@const
@TODO:

*/
private const ORDER_DEFAULT = ['param', 'throws', 'return'];

public function getDefinition(): FixerDefinitionInterface
{
$code = <<<'EOF'
            <?php
            /**
             * Hello there!
             *
             * @throws Exception|RuntimeException foo
             * @custom Test!
             * @return int  Return the number of changes.
             * @param string $foo
             * @param bool   $bar Bar
             */

            EOF;

return new FixerDefinition(
'Annotations in PHPDoc should be ordered in defined sequence.',
[
new CodeSample($code),
new CodeSample($code, ['order' => self::ORDER_DEFAULT]),
new CodeSample($code, ['order' => ['param', 'return', 'throws']]),
new CodeSample($code, ['order' => ['param', 'custom', 'throws', 'return']]),
],
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}







public function getPriority(): int
{
return -2;
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('order', 'Sequence in which annotations in PHPDoc should be ordered.'))
->setAllowedTypes(['string[]'])
->setAllowedValues([static function (array $order): bool {
if (\count($order) < 2) {
throw new InvalidOptionsException('The option "order" value is invalid. Minimum two tags are required.');
}

return true;
}])
->setDefault(self::ORDER_DEFAULT)
->getOption(),
]);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_DOC_COMMENT)) {
continue;
}


$content = $token->getContent();



$successors = $this->configuration['order'];
while (\count($successors) >= 3) {
$predecessor = array_shift($successors);
$content = $this->moveAnnotationsBefore($predecessor, $successors, $content);
}




$predecessors = $this->configuration['order'];
$last = array_pop($predecessors);
$content = $this->moveAnnotationsAfter($last, $predecessors, $content);


$tokens[$index] = new Token([T_DOC_COMMENT, $content]);
}
}







private function moveAnnotationsBefore(string $move, array $before, string $content): string
{
$doc = new DocBlock($content);
$toBeMoved = $doc->getAnnotationsOfType($move);


if (0 === \count($toBeMoved)) {
return $content;
}

$others = $doc->getAnnotationsOfType($before);

if (0 === \count($others)) {
return $content;
}


$end = end($toBeMoved)->getEnd();

$line = $doc->getLine($end);


foreach ($others as $other) {
if ($other->getStart() < $end) {

$line->setContent($line->getContent().$other->getContent());
$other->remove();
}
}

return $doc->getContent();
}







private function moveAnnotationsAfter(string $move, array $after, string $content): string
{
$doc = new DocBlock($content);
$toBeMoved = $doc->getAnnotationsOfType($move);


if (0 === \count($toBeMoved)) {
return $content;
}

$others = $doc->getAnnotationsOfType($after);


if (0 === \count($others)) {
return $content;
}


$start = $toBeMoved[0]->getStart();
$line = $doc->getLine($start);


foreach (array_reverse($others) as $other) {
if ($other->getEnd() > $start) {

$line->setContent($other->getContent().$line->getContent());
$other->remove();
}
}

return $doc->getContent();
}
}
