<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Output;

use Symfony\Component\Console\Output\OutputInterface;




final class OutputContext
{
private ?OutputInterface $output;
private int $terminalWidth;
private int $filesCount;

public function __construct(
?OutputInterface $output,
int $terminalWidth,
int $filesCount
) {
$this->output = $output;
$this->terminalWidth = $terminalWidth;
$this->filesCount = $filesCount;
}

public function getOutput(): ?OutputInterface
{
return $this->output;
}

public function getTerminalWidth(): int
{
return $this->terminalWidth;
}

public function getFilesCount(): int
{
return $this->filesCount;
}
}
