<?php

declare(strict_types=1);











namespace PhpCsFixer\Linter;

use Symfony\Component\Process\Process;






final class ProcessLintingResult implements LintingResultInterface
{
private Process $process;

private ?string $path;

private ?bool $isSuccessful = null;

public function __construct(Process $process, ?string $path = null)
{
$this->process = $process;
$this->path = $path;
}

public function check(): void
{
if (!$this->isSuccessful()) {

throw new LintingException($this->getProcessErrorMessage(), $this->process->getExitCode());
}
}

private function getProcessErrorMessage(): string
{
$errorOutput = $this->process->getErrorOutput();
$output = strtok(ltrim('' !== $errorOutput ? $errorOutput : $this->process->getOutput()), "\n");

if (false === $output) {
return 'Fatal error: Unable to lint file.';
}

if (null !== $this->path) {
$needle = \sprintf('in %s ', $this->path);
$pos = strrpos($output, $needle);

if (false !== $pos) {
$output = \sprintf('%s%s', substr($output, 0, $pos), substr($output, $pos + \strlen($needle)));
}
}

$prefix = substr($output, 0, 18);

if ('PHP Parse error:  ' === $prefix) {
return \sprintf('Parse error: %s.', substr($output, 18));
}

if ('PHP Fatal error:  ' === $prefix) {
return \sprintf('Fatal error: %s.', substr($output, 18));
}

return \sprintf('%s.', $output);
}

private function isSuccessful(): bool
{
if (null === $this->isSuccessful) {
$this->process->wait();
$this->isSuccessful = $this->process->isSuccessful();
}

return $this->isSuccessful;
}
}
