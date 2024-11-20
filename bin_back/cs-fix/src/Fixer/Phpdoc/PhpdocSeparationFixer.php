<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\Annotation;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
@implements
@phpstan-type
@phpstan-type










*/
final class PhpdocSeparationFixer extends AbstractFixer implements ConfigurableFixerInterface
{
/**
@use */
use ConfigurableFixerTrait;






public const OPTION_GROUPS_DEFAULT = [
['author', 'copyright', 'license'],
['category', 'package', 'subpackage'],
['property', 'property-read', 'property-write'],
['deprecated', 'link', 'see', 'since'],
];




private array $groups;

public function getDefinition(): FixerDefinitionInterface
{
$code = <<<'EOF'
            <?php
            /**
             * Hello there!
             *
             * @author John Doe
             * @custom Test!
             *
             * @throws Exception|RuntimeException foo
             * @param string $foo
             *
             * @param bool   $bar Bar
             * @return int  Return the number of changes.
             */

            EOF;

return new FixerDefinition(
'Annotations in PHPDoc should be grouped together so that annotations of the same type immediately follow each other. Annotations of a different type are separated by a single blank line.',
[
new CodeSample($code),
new CodeSample($code, ['groups' => [
['deprecated', 'link', 'see', 'since'],
['author', 'copyright', 'license'],
['category', 'package', 'subpackage'],
['property', 'property-read', 'property-write'],
['param', 'return'],
]]),
new CodeSample($code, ['groups' => [
['author', 'throws', 'custom'],
['return', 'param'],
]]),
new CodeSample(
<<<'EOF'
                        <?php
                        /**
                         * @ORM\Id
                         *
                         * @ORM\GeneratedValue
                         * @Assert\NotNull
                         *
                         * @Assert\Type("string")
                         */

                        EOF,
['groups' => [['ORM\*'], ['Assert\*']]],
),
new CodeSample($code, ['skip_unlisted_annotations' => true]),
],
);
}







public function getPriority(): int
{
return -3;
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}

protected function configurePostNormalisation(): void
{
$this->groups = $this->configuration['groups'];
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_DOC_COMMENT)) {
continue;
}

$doc = new DocBlock($token->getContent());
$this->fixDescription($doc);
$this->fixAnnotations($doc);

$tokens[$index] = new Token([T_DOC_COMMENT, $doc->getContent()]);
}
}

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
$allowTagToBelongToOnlyOneGroup = static function (array $groups): bool {
$tags = [];
foreach ($groups as $groupIndex => $group) {
foreach ($group as $member) {
if (isset($tags[$member])) {
if ($groupIndex === $tags[$member]) {
throw new InvalidOptionsException(
'The option "groups" value is invalid. '.
'The "'.$member.'" tag is specified more than once.'
);
}

throw new InvalidOptionsException(
'The option "groups" value is invalid. '.
'The "'.$member.'" tag belongs to more than one group.'
);
}
$tags[$member] = $groupIndex;
}
}

return true;
};

return new FixerConfigurationResolver([
(new FixerOptionBuilder('groups', 'Sets of annotation types to be grouped together. Use `*` to match any tag character.'))
->setAllowedTypes(['string[][]'])
->setDefault(self::OPTION_GROUPS_DEFAULT)
->setAllowedValues([$allowTagToBelongToOnlyOneGroup])
->getOption(),
(new FixerOptionBuilder('skip_unlisted_annotations', 'Whether to skip annotations that are not listed in any group.'))
->setAllowedTypes(['bool'])
->setDefault(false) 
->getOption(),
]);
}




private function fixDescription(DocBlock $doc): void
{
foreach ($doc->getLines() as $index => $line) {
if ($line->containsATag()) {
break;
}

if ($line->containsUsefulContent()) {
$next = $doc->getLine($index + 1);

if (null !== $next && $next->containsATag()) {
$line->addBlank();

break;
}
}
}
}




private function fixAnnotations(DocBlock $doc): void
{
foreach ($doc->getAnnotations() as $index => $annotation) {
$next = $doc->getAnnotation($index + 1);

if (null === $next) {
break;
}

$shouldBeTogether = $this->shouldBeTogether($annotation, $next, $this->groups);

if (true === $shouldBeTogether) {
$this->ensureAreTogether($doc, $annotation, $next);
} elseif (false === $shouldBeTogether || false === $this->configuration['skip_unlisted_annotations']) {
$this->ensureAreSeparate($doc, $annotation, $next);
}
}
}




private function ensureAreTogether(DocBlock $doc, Annotation $first, Annotation $second): void
{
$pos = $first->getEnd();
$final = $second->getStart();

for (++$pos; $pos < $final; ++$pos) {
$doc->getLine($pos)->remove();
}
}




private function ensureAreSeparate(DocBlock $doc, Annotation $first, Annotation $second): void
{
$pos = $first->getEnd();
$final = $second->getStart() - 1;


if ($pos === $final) {
$doc->getLine($pos)->addBlank();

return;
}

for (++$pos; $pos < $final; ++$pos) {
$doc->getLine($pos)->remove();
}
}




private function shouldBeTogether(Annotation $first, Annotation $second, array $groups): ?bool
{
$firstName = $this->tagName($first);
$secondName = $this->tagName($second);


if (null === $firstName || null === $secondName) {
return null;
}

if ($firstName === $secondName) {
return true;
}

foreach ($groups as $group) {
$firstTagIsInGroup = $this->isInGroup($firstName, $group);
$secondTagIsInGroup = $this->isInGroup($secondName, $group);

if ($firstTagIsInGroup) {
return $secondTagIsInGroup;
}

if ($secondTagIsInGroup) {
return false;
}
}

return null;
}

private function tagName(Annotation $annotation): ?string
{
Preg::match('/@([a-zA-Z0-9_\\\-]+(?=\s|$|\())/', $annotation->getContent(), $matches);

return $matches[1] ?? null;
}




private function isInGroup(string $tag, array $group): bool
{
foreach ($group as $tagInGroup) {
$tagInGroup = str_replace('*', '\*', $tagInGroup);
$tagInGroup = preg_quote($tagInGroup, '/');
$tagInGroup = str_replace('\\\\\*', '.*?', $tagInGroup);

if (Preg::match("/^{$tagInGroup}$/", $tag)) {
return true;
}
}

return false;
}
}
