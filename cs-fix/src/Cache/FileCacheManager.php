<?php

declare(strict_types=1);











namespace PhpCsFixer\Cache;

use PhpCsFixer\Tokenizer\CodeHasher;

















final class FileCacheManager implements CacheManagerInterface
{
public const WRITE_FREQUENCY = 10;

private FileHandlerInterface $handler;

private SignatureInterface $signature;

private bool $isDryRun;

private DirectoryInterface $cacheDirectory;

private int $writeCounter = 0;

private bool $signatureWasUpdated = false;




private $cache;

public function __construct(
FileHandlerInterface $handler,
SignatureInterface $signature,
bool $isDryRun = false,
?DirectoryInterface $cacheDirectory = null
) {
$this->handler = $handler;
$this->signature = $signature;
$this->isDryRun = $isDryRun;
$this->cacheDirectory = $cacheDirectory ?? new Directory('');

$this->readCache();
}

public function __destruct()
{
if (true === $this->signatureWasUpdated || 0 !== $this->writeCounter) {
$this->writeCache();
}
}





public function __sleep(): array
{
throw new \BadMethodCallException('Cannot serialize '.__CLASS__);
}







public function __wakeup(): void
{
throw new \BadMethodCallException('Cannot unserialize '.__CLASS__);
}

public function needFixing(string $file, string $fileContent): bool
{
$file = $this->cacheDirectory->getRelativePathTo($file);

return !$this->cache->has($file) || $this->cache->get($file) !== $this->calcHash($fileContent);
}

public function setFile(string $file, string $fileContent): void
{
$this->setFileHash($file, $this->calcHash($fileContent));
}

public function setFileHash(string $file, string $hash): void
{
$file = $this->cacheDirectory->getRelativePathTo($file);

if ($this->isDryRun && $this->cache->has($file) && $this->cache->get($file) !== $hash) {
$this->cache->clear($file);
} else {
$this->cache->set($file, $hash);
}

if (self::WRITE_FREQUENCY === ++$this->writeCounter) {
$this->writeCounter = 0;
$this->writeCache();
}
}

private function readCache(): void
{
$cache = $this->handler->read();

if (null === $cache || !$this->signature->equals($cache->getSignature())) {
$cache = new Cache($this->signature);
$this->signatureWasUpdated = true;
}

$this->cache = $cache;
}

private function writeCache(): void
{
$this->handler->write($this->cache);
}

private function calcHash(string $content): string
{
return CodeHasher::calculateCodeHash($content);
}
}
