<?php

declare(strict_types=1);











namespace PhpCsFixer\Linter;






final class CachingLinter implements LinterInterface
{
private LinterInterface $sublinter;




private array $cache = [];

public function __construct(LinterInterface $linter)
{
$this->sublinter = $linter;
}

public function isAsync(): bool
{
return $this->sublinter->isAsync();
}

public function lintFile(string $path): LintingResultInterface
{
$checksum = md5(file_get_contents($path));

if (!isset($this->cache[$checksum])) {
$this->cache[$checksum] = $this->sublinter->lintFile($path);
}

return $this->cache[$checksum];
}

public function lintSource(string $source): LintingResultInterface
{
$checksum = md5($source);

if (!isset($this->cache[$checksum])) {
$this->cache[$checksum] = $this->sublinter->lintSource($source);
}

return $this->cache[$checksum];
}
}
