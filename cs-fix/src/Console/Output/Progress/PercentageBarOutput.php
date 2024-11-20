<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Output\Progress;

use PhpCsFixer\Console\Output\OutputContext;
use PhpCsFixer\FixerFileProcessedEvent;
use Symfony\Component\Console\Helper\ProgressBar;






final class PercentageBarOutput implements ProgressOutputInterface
{
/**
@readonly */
private OutputContext $context;

private ProgressBar $progressBar;

public function __construct(OutputContext $context)
{
$this->context = $context;

$this->progressBar = new ProgressBar($context->getOutput(), $this->context->getFilesCount());
$this->progressBar->setBarCharacter('▓'); 
$this->progressBar->setEmptyBarCharacter('░'); 
$this->progressBar->setProgressCharacter('');
$this->progressBar->setFormat('normal');

$this->progressBar->start();
}





public function __sleep(): array
{
throw new \BadMethodCallException('Cannot serialize '.self::class);
}







public function __wakeup(): void
{
throw new \BadMethodCallException('Cannot unserialize '.self::class);
}

public function onFixerFileProcessed(FixerFileProcessedEvent $event): void
{
$this->progressBar->advance(1);

if ($this->progressBar->getProgress() === $this->progressBar->getMaxSteps()) {
$this->context->getOutput()->write("\n\n");
}
}

public function printLegend(): void {}
}
