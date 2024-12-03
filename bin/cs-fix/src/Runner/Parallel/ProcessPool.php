<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner\Parallel;

use React\Socket\ServerInterface;









final class ProcessPool
{
private ServerInterface $server;

/** @var null|(callable(): void) */
private $onServerClose;


private array $processes = [];

/**
     * @param null|(callable(): void) $onServerClose
     */
public function __construct(ServerInterface $server, ?callable $onServerClose = null)
{
$this->server = $server;
$this->onServerClose = $onServerClose;
}

public function getProcess(ProcessIdentifier $identifier): Process
{
if (!isset($this->processes[$identifier->toString()])) {
throw ParallelisationException::forUnknownIdentifier($identifier);
}

return $this->processes[$identifier->toString()];
}

public function addProcess(ProcessIdentifier $identifier, Process $process): void
{
$this->processes[$identifier->toString()] = $process;
}

public function endProcessIfKnown(ProcessIdentifier $identifier): void
{
if (!isset($this->processes[$identifier->toString()])) {
return;
}

$this->endProcess($identifier);
}

public function endAll(): void
{
foreach (array_keys($this->processes) as $identifier) {
$this->endProcessIfKnown(ProcessIdentifier::fromRaw($identifier));
}
}

private function endProcess(ProcessIdentifier $identifier): void
{
$this->getProcess($identifier)->quit();

unset($this->processes[$identifier->toString()]);

if (0 === \count($this->processes)) {
$this->server->close();

if (null !== $this->onServerClose) {
($this->onServerClose)();
}
}
}
}
