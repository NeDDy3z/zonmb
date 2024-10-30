<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\SelfUpdate;




interface NewVersionCheckerInterface
{



public function getLatestVersion(): string;




public function getLatestVersionOfMajor(int $majorVersion): ?string;





public function compareVersions(string $versionA, string $versionB): int;
}
