<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\Doctrine\Annotation\Tokens as DoctrineAnnotationTokens;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
@phpstan-type
@phpstan-type
@implements









*/
abstract class AbstractDoctrineAnnotationFixer extends AbstractFixer implements ConfigurableFixerInterface
{



private array $classyElements;

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{

$analyzer = new TokensAnalyzer($tokens);
$this->classyElements = $analyzer->getClassyElements();


foreach ($tokens->findGivenKind(T_DOC_COMMENT) as $index => $docCommentToken) {
if (!$this->nextElementAcceptsDoctrineAnnotations($tokens, $index)) {
continue;
}

$doctrineAnnotationTokens = DoctrineAnnotationTokens::createFromDocComment(
$docCommentToken,
$this->configuration['ignored_tags'] 
);

$this->fixAnnotations($doctrineAnnotationTokens);
$tokens[$index] = new Token([T_DOC_COMMENT, $doctrineAnnotationTokens->getCode()]);
}
}




abstract protected function fixAnnotations(DoctrineAnnotationTokens $doctrineAnnotationTokens): void;

protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
{
return new FixerConfigurationResolver([
(new FixerOptionBuilder('ignored_tags', 'List of tags that must not be treated as Doctrine Annotations.'))
->setAllowedTypes(['string[]'])
->setDefault([

'abstract',
'access',
'code',
'deprec',
'encode',
'exception',
'final',
'ingroup',
'inheritdoc',
'inheritDoc',
'magic',
'name',
'toc',
'tutorial',
'private',
'static',
'staticvar',
'staticVar',
'throw',


'api',
'author',
'category',
'copyright',
'deprecated',
'example',
'filesource',
'global',
'ignore',
'internal',
'license',
'link',
'method',
'package',
'param',
'property',
'property-read',
'property-write',
'return',
'see',
'since',
'source',
'subpackage',
'throws',
'todo',
'TODO',
'usedBy',
'uses',
'var',
'version',


'after',
'afterClass',
'backupGlobals',
'backupStaticAttributes',
'before',
'beforeClass',
'codeCoverageIgnore',
'codeCoverageIgnoreStart',
'codeCoverageIgnoreEnd',
'covers',
'coversDefaultClass',
'coversNothing',
'dataProvider',
'depends',
'expectedException',
'expectedExceptionCode',
'expectedExceptionMessage',
'expectedExceptionMessageRegExp',
'group',
'large',
'medium',
'preserveGlobalState',
'requires',
'runTestsInSeparateProcesses',
'runInSeparateProcess',
'small',
'test',
'testdox',
'ticket',
'uses',


'SuppressWarnings',


'noinspection',


'package_version',


'enduml',
'startuml',


'psalm',


'phpstan',
'template',


'fix',
'FIXME',
'fixme',
'override',
])
->getOption(),
]);
}

private function nextElementAcceptsDoctrineAnnotations(Tokens $tokens, int $index): bool
{
$classModifiers = [T_ABSTRACT, T_FINAL];

if (\defined('T_READONLY')) { 
$classModifiers[] = T_READONLY;
}

do {
$index = $tokens->getNextMeaningfulToken($index);

if (null === $index) {
return false;
}
} while ($tokens[$index]->isGivenKind($classModifiers));

if ($tokens[$index]->isGivenKind(T_CLASS)) {
return true;
}

$modifierKinds = [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_FINAL, T_ABSTRACT, T_NS_SEPARATOR, T_STRING, CT::T_NULLABLE_TYPE];

if (\defined('T_READONLY')) { 
$modifierKinds[] = T_READONLY;
}

while ($tokens[$index]->isGivenKind($modifierKinds)) {
$index = $tokens->getNextMeaningfulToken($index);
}

if (!isset($this->classyElements[$index])) {
return false;
}

return $tokens[$this->classyElements[$index]['classIndex']]->isGivenKind(T_CLASS); 
}
}
