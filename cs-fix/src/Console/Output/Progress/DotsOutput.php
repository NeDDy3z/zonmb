<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Output\Progress;

use PhpCsFixer\Console\Output\OutputContext;
use PhpCsFixer\FixerFileProcessedEvent;
use Symfony\Component\Console\Output\OutputInterface;






final class DotsOutput implements ProgressOutputInterface
{





private static array $eventStatusMap = [
FixerFileProcessedEvent::STATUS_NO_CHANGES => ['symbol' => '.', 'format' => '%s', 'description' => 'no changes'],
FixerFileProcessedEvent::STATUS_FIXED => ['symbol' => 'F', 'format' => '<fg=green>%s</fg=green>', 'description' => 'fixed'],
FixerFileProcessedEvent::STATUS_SKIPPED => ['symbol' => 'S', 'format' => '<fg=cyan>%s</fg=cyan>', 'description' => 'skipped (cached or empty file)'],
FixerFileProcessedEvent::STATUS_INVALID => ['symbol' => 'I', 'format' => '<bg=red>%s</bg=red>', 'description' => 'invalid file syntax (file ignored)'],
FixerFileProcessedEvent::STATUS_EXCEPTION => ['symbol' => 'E', 'format' => '<bg=red>%s</bg=red>', 'description' => 'error'],
FixerFileProcessedEvent::STATUS_LINT => ['symbol' => 'E', 'format' => '<bg=red>%s</bg=red>', 'description' => 'error'],
];

/**
@readonly */
private OutputContext $context;

private int $processedFiles = 0;




private $symbolsPerLine;

public function __construct(OutputContext $context)
{
$this->context = $context;




$this->symbolsPerLine = max(1, $context->getTerminalWidth() - \strlen((string) $context->getFilesCount()) * 2 - 11);
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
$status = self::$eventStatusMap[$event->getStatus()];
$this->getOutput()->write($this->getOutput()->isDecorated() ? \sprintf($status['format'], $status['symbol']) : $status['symbol']);

++$this->processedFiles;

$symbolsOnCurrentLine = $this->processedFiles % $this->symbolsPerLine;
$isLast = $this->processedFiles === $this->context->getFilesCount();

if (0 === $symbolsOnCurrentLine || $isLast) {
$this->getOutput()->write(\sprintf(
'%s %'.\strlen((string) $this->context->getFilesCount()).'d / %d (%3d%%)',
$isLast && 0 !== $symbolsOnCurrentLine ? str_repeat(' ', $this->symbolsPerLine - $symbolsOnCurrentLine) : '',
$this->processedFiles,
$this->context->getFilesCount(),
round($this->processedFiles / $this->context->getFilesCount() * 100)
));

if (!$isLast) {
$this->getOutput()->writeln('');
}
}
}

public function printLegend(): void
{
$symbols = [];

foreach (self::$eventStatusMap as $status) {
$symbol = $status['symbol'];
if ('' === $symbol || isset($symbols[$symbol])) {
continue;
}

$symbols[$symbol] = \sprintf('%s-%s', $this->getOutput()->isDecorated() ? \sprintf($status['format'], $symbol) : $symbol, $status['description']);
}

$this->getOutput()->write(\sprintf("\nLegend: %s\n", implode(', ', $symbols)));
}

private function getOutput(): OutputInterface
{
return $this->context->getOutput();
}
}
