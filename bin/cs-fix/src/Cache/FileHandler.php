<?php

declare(strict_types=1);











namespace PhpCsFixer\Cache;

use Symfony\Component\Filesystem\Exception\IOException;







final class FileHandler implements FileHandlerInterface
{
private \SplFileInfo $fileInfo;

private int $fileMTime = 0;

public function __construct(string $file)
{
$this->fileInfo = new \SplFileInfo($file);
}

public function getFile(): string
{
return $this->fileInfo->getPathname();
}

public function read(): ?CacheInterface
{
if (!$this->fileInfo->isFile() || !$this->fileInfo->isReadable()) {
return null;
}

$fileObject = $this->fileInfo->openFile('r');

$cache = $this->readFromHandle($fileObject);
$this->fileMTime = $this->getFileCurrentMTime();

unset($fileObject); 

return $cache;
}

public function write(CacheInterface $cache): void
{
$this->ensureFileIsWriteable();

$fileObject = $this->fileInfo->openFile('r+');

if (method_exists($cache, 'backfillHashes') && $this->fileMTime < $this->getFileCurrentMTime()) {
$resultOfFlock = $fileObject->flock(LOCK_EX);
if (false === $resultOfFlock) {


}

$oldCache = $this->readFromHandle($fileObject);

$fileObject->rewind();

if (null !== $oldCache) {
$cache->backfillHashes($oldCache);
}
}

$resultOfTruncate = $fileObject->ftruncate(0);
if (false === $resultOfTruncate) {

return;
}

$resultOfWrite = $fileObject->fwrite($cache->toJson());
if (false === $resultOfWrite) {

return;
}

$resultOfFlush = $fileObject->fflush();
if (false === $resultOfFlush) {


}

$this->fileMTime = time(); 
}

private function getFileCurrentMTime(): int
{
clearstatcache(true, $this->fileInfo->getPathname());

$mtime = $this->fileInfo->getMTime();

if (false === $mtime) {

$mtime = 0;
}

return $mtime;
}

private function readFromHandle(\SplFileObject $fileObject): ?CacheInterface
{
try {
$size = $fileObject->getSize();
if (false === $size || 0 === $size) {
return null;
}

$content = $fileObject->fread($size);

if (false === $content) {
return null;
}

return Cache::fromJson($content);
} catch (\InvalidArgumentException $exception) {
return null;
}
}

private function ensureFileIsWriteable(): void
{
if ($this->fileInfo->isFile() && $this->fileInfo->isWritable()) {

return;
}

if ($this->fileInfo->isDir()) {
throw new IOException(
\sprintf('Cannot write cache file "%s" as the location exists as directory.', $this->fileInfo->getRealPath()),
0,
null,
$this->fileInfo->getPathname()
);
}

if ($this->fileInfo->isFile() && !$this->fileInfo->isWritable()) {
throw new IOException(
\sprintf('Cannot write to file "%s" as it is not writable.', $this->fileInfo->getRealPath()),
0,
null,
$this->fileInfo->getPathname()
);
}

$this->createFile($this->fileInfo->getPathname());
}

private function createFile(string $file): void
{
$dir = \dirname($file);



if (!@is_dir($dir)) {
@mkdir($dir, 0777, true);
}

if (!@is_dir($dir)) {
throw new IOException(
\sprintf('Directory of cache file "%s" does not exists and couldn\'t be created.', $file),
0,
null,
$file
);
}

@touch($file);
@chmod($file, 0666);
}
}
