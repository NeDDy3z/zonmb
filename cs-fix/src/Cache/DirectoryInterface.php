<?php

declare(strict_types=1);











namespace PhpCsFixer\Cache;




interface DirectoryInterface
{
public function getRelativePathTo(string $file): string;
}
