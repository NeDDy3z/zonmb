<?php

declare(strict_types=1);











namespace PhpCsFixer;









final class FileRemoval
{





private array $files = [];

public function __construct()
{
register_shutdown_function([$this, 'clean']);
}

public function __destruct()
{
$this->clean();
}





public function __sleep(): array
{
throw new \BadMethodCallException('Cannot serialize '.self::class);
}







public function __wakeup(): void
{
throw new \BadMethodCallException('Cannot unserialize '.self::class);
}




public function observe(string $path): void
{
$this->files[$path] = true;
}




public function delete(string $path): void
{
if (isset($this->files[$path])) {
unset($this->files[$path]);
}

$this->unlink($path);
}




public function clean(): void
{
foreach ($this->files as $file => $value) {
$this->unlink($file);
}

$this->files = [];
}

private function unlink(string $path): void
{
@unlink($path);
}
}
