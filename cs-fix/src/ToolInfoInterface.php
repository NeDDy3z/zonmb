<?php

declare(strict_types=1);











namespace PhpCsFixer;




interface ToolInfoInterface
{



public function getComposerInstallationDetails(): array;

public function getComposerVersion(): string;

public function getVersion(): string;

public function isInstalledAsPhar(): bool;

public function isInstalledByComposer(): bool;

public function isRunInsideDocker(): bool;

public function getPharDownloadUri(string $version): string;
}
