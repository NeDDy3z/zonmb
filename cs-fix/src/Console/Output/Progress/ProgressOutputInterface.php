<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Output\Progress;

use PhpCsFixer\FixerFileProcessedEvent;




interface ProgressOutputInterface
{
public function printLegend(): void;

public function onFixerFileProcessed(FixerFileProcessedEvent $event): void;
}
