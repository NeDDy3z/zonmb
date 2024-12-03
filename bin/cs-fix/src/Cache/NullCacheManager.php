<?php

declare(strict_types=1);











namespace PhpCsFixer\Cache;







final class NullCacheManager implements CacheManagerInterface
{
public function needFixing(string $file, string $fileContent): bool
{
return true;
}

public function setFile(string $file, string $fileContent): void {}

public function setFileHash(string $file, string $hash): void {}
}
