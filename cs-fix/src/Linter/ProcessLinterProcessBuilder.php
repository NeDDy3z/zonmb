<?php

declare(strict_types=1);











namespace PhpCsFixer\Linter;

use Symfony\Component\Process\Process;






final class ProcessLinterProcessBuilder
{
private string $executable;




public function __construct(string $executable)
{
$this->executable = $executable;
}

public function build(string $path): Process
{
return new Process([
$this->executable,
'-l',
$path,
]);
}
}
