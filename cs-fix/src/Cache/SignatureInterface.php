<?php

declare(strict_types=1);











namespace PhpCsFixer\Cache;






interface SignatureInterface
{
public function getPhpVersion(): string;

public function getFixerVersion(): string;

public function getIndent(): string;

public function getLineEnding(): string;




public function getRules(): array;

public function equals(self $signature): bool;
}
