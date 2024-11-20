<?php

declare(strict_types=1);











namespace PhpCsFixer;










final class FileReader
{



private $stdinContent;

public static function createSingleton(): self
{
static $instance = null;

if (!$instance) {
$instance = new self();
}

return $instance;
}

public function read(string $filePath): string
{
if ('php://stdin' === $filePath) {
if (null === $this->stdinContent) {
$this->stdinContent = $this->readRaw($filePath);
}

return $this->stdinContent;
}

return $this->readRaw($filePath);
}

private function readRaw(string $realPath): string
{
$content = @file_get_contents($realPath);

if (false === $content) {
$error = error_get_last();

throw new \RuntimeException(\sprintf(
'Failed to read content from "%s".%s',
$realPath,
null !== $error ? ' '.$error['message'] : ''
));
}

return $content;
}
}
