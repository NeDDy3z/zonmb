<?php

declare(strict_types=1);











namespace PhpCsFixer\Linter;

use PhpCsFixer\FileReader;
use PhpCsFixer\FileRemoval;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;








final class ProcessLinter implements LinterInterface
{
private FileRemoval $fileRemoval;

private ProcessLinterProcessBuilder $processBuilder;






private $temporaryFile;




public function __construct(?string $executable = null)
{
if (null === $executable) {
$executableFinder = new PhpExecutableFinder();
$executable = $executableFinder->find(false);

if (false === $executable) {
throw new UnavailableLinterException('Cannot find PHP executable.');
}

if ('phpdbg' === \PHP_SAPI) {
if (!str_contains($executable, 'phpdbg')) {
throw new UnavailableLinterException('Automatically found PHP executable is non-standard phpdbg. Could not find proper PHP executable.');
}


$executable = str_replace('phpdbg', 'php', $executable);

if (!is_executable($executable)) {
throw new UnavailableLinterException('Automatically found PHP executable is phpdbg. Could not find proper PHP executable.');
}
}
}

$this->processBuilder = new ProcessLinterProcessBuilder($executable);
$this->fileRemoval = new FileRemoval();
}

public function __destruct()
{
if (null !== $this->temporaryFile) {
$this->fileRemoval->delete($this->temporaryFile);
}
}





public function __sleep(): array
{
throw new \BadMethodCallException('Cannot serialize '.self::class);
}







public function __wakeup(): void
{
throw new \BadMethodCallException('Cannot unserialize '.self::class);
}

public function isAsync(): bool
{
return true;
}

public function lintFile(string $path): LintingResultInterface
{
return new ProcessLintingResult($this->createProcessForFile($path), $path);
}

public function lintSource(string $source): LintingResultInterface
{
return new ProcessLintingResult($this->createProcessForSource($source), $this->temporaryFile);
}




private function createProcessForFile(string $path): Process
{

if (!is_file($path)) {
return $this->createProcessForSource(FileReader::createSingleton()->read($path));
}

$process = $this->processBuilder->build($path);
$process->setTimeout(10);
$process->start();

return $process;
}






private function createProcessForSource(string $source): Process
{
if (null === $this->temporaryFile) {
$this->temporaryFile = tempnam(sys_get_temp_dir(), 'cs_fixer_tmp_');
$this->fileRemoval->observe($this->temporaryFile);
}

if (false === @file_put_contents($this->temporaryFile, $source)) {
throw new IOException(\sprintf('Failed to write file "%s".', $this->temporaryFile), 0, null, $this->temporaryFile);
}

return $this->createProcessForFile($this->temporaryFile);
}
}
