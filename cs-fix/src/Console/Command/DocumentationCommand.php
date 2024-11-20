<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Command;

use PhpCsFixer\Documentation\DocumentationLocator;
use PhpCsFixer\Documentation\FixerDocumentGenerator;
use PhpCsFixer\Documentation\RuleSetDocumentationGenerator;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\RuleSet\RuleSets;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;




#[AsCommand(name: 'documentation')]
final class DocumentationCommand extends Command
{

protected static $defaultName = 'documentation';

private Filesystem $filesystem;

public function __construct(Filesystem $filesystem)
{
parent::__construct();
$this->filesystem = $filesystem;
}

protected function configure(): void
{
$this
->setAliases(['doc'])
->setDescription('Dumps the documentation of the project into its "/doc" directory.')
;
}

protected function execute(InputInterface $input, OutputInterface $output): int
{
$locator = new DocumentationLocator();

$fixerFactory = new FixerFactory();
$fixerFactory->registerBuiltInFixers();
$fixers = $fixerFactory->getFixers();

$setDefinitions = RuleSets::getSetDefinitions();

$fixerDocumentGenerator = new FixerDocumentGenerator($locator);
$ruleSetDocumentationGenerator = new RuleSetDocumentationGenerator($locator);





$docForFixerRelativePaths = [];

foreach ($fixers as $fixer) {
$docForFixerRelativePaths[] = $locator->getFixerDocumentationFileRelativePath($fixer);
$this->filesystem->dumpFile(
$locator->getFixerDocumentationFilePath($fixer),
$fixerDocumentGenerator->generateFixerDocumentation($fixer)
);
}


foreach (
(new Finder())->files()
->in($locator->getFixersDocumentationDirectoryPath())
->notPath($docForFixerRelativePaths) as $file
) {
$this->filesystem->remove($file->getPathname());
}



$this->filesystem->dumpFile(
$locator->getFixersDocumentationIndexFilePath(),
$fixerDocumentGenerator->generateFixersDocumentationIndex($fixers)
);




foreach ((new Finder())->files()->in($locator->getRuleSetsDocumentationDirectoryPath()) as $file) {
$this->filesystem->remove($file->getPathname());
}

$paths = [];

foreach ($setDefinitions as $name => $definition) {
$path = $locator->getRuleSetsDocumentationFilePath($name);
$paths[$path] = $definition;
$this->filesystem->dumpFile($path, $ruleSetDocumentationGenerator->generateRuleSetsDocumentation($definition, $fixers));
}



$this->filesystem->dumpFile(
$locator->getRuleSetsDocumentationIndexFilePath(),
$ruleSetDocumentationGenerator->generateRuleSetsDocumentationIndex($paths)
);

$output->writeln('Docs updated.');

return 0;
}
}
