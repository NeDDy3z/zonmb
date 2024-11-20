<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner;

use PhpCsFixer\Cache\CacheManagerInterface;
use PhpCsFixer\FileReader;
use PhpCsFixer\FixerFileProcessedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
@extends




*/
final class FileFilterIterator extends \FilterIterator
{
private ?EventDispatcherInterface $eventDispatcher;

private CacheManagerInterface $cacheManager;




private array $visitedElements = [];




public function __construct(
\Traversable $iterator,
?EventDispatcherInterface $eventDispatcher,
CacheManagerInterface $cacheManager
) {
if (!$iterator instanceof \Iterator) {
$iterator = new \IteratorIterator($iterator);
}

parent::__construct($iterator);

$this->eventDispatcher = $eventDispatcher;
$this->cacheManager = $cacheManager;
}

public function accept(): bool
{
$file = $this->current();
if (!$file instanceof \SplFileInfo) {
throw new \RuntimeException(
\sprintf(
'Expected instance of "\SplFileInfo", got "%s".',
get_debug_type($file)
)
);
}

$path = $file->isLink() ? $file->getPathname() : $file->getRealPath();

if (isset($this->visitedElements[$path])) {
return false;
}

$this->visitedElements[$path] = true;

if (!$file->isFile() || $file->isLink()) {
return false;
}

$content = FileReader::createSingleton()->read($path);


if (

'' === $content

|| !$this->cacheManager->needFixing($file->getPathname(), $content)
) {
$this->dispatchEvent(
FixerFileProcessedEvent::NAME,
new FixerFileProcessedEvent(FixerFileProcessedEvent::STATUS_SKIPPED)
);

return false;
}

return true;
}

private function dispatchEvent(string $name, Event $event): void
{
if (null === $this->eventDispatcher) {
return;
}

$this->eventDispatcher->dispatch($event, $name);
}
}
