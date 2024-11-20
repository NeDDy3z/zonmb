<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\Console\Application;








final class ToolInfo implements ToolInfoInterface
{
public const COMPOSER_PACKAGE_NAME = 'friendsofphp/php-cs-fixer';

public const COMPOSER_LEGACY_PACKAGE_NAME = 'fabpot/php-cs-fixer';




private $composerInstallationDetails;




private $isInstalledByComposer;

public function getComposerInstallationDetails(): array
{
if (!$this->isInstalledByComposer()) {
throw new \LogicException('Cannot get composer version for tool not installed by composer.');
}

if (null === $this->composerInstallationDetails) {
$composerInstalled = json_decode(file_get_contents($this->getComposerInstalledFile()), true, 512, JSON_THROW_ON_ERROR);

$packages = $composerInstalled['packages'] ?? $composerInstalled;

foreach ($packages as $package) {
if (\in_array($package['name'], [self::COMPOSER_PACKAGE_NAME, self::COMPOSER_LEGACY_PACKAGE_NAME], true)) {
$this->composerInstallationDetails = $package;

break;
}
}
}

return $this->composerInstallationDetails;
}

public function getComposerVersion(): string
{
$package = $this->getComposerInstallationDetails();

$versionSuffix = '';

if (isset($package['dist']['reference'])) {
$versionSuffix = '#'.$package['dist']['reference'];
}

return $package['version'].$versionSuffix;
}

public function getVersion(): string
{
if ($this->isInstalledByComposer()) {
return Application::VERSION.':'.$this->getComposerVersion();
}

return Application::VERSION;
}

public function isInstalledAsPhar(): bool
{
return str_starts_with(__DIR__, 'phar://');
}

public function isInstalledByComposer(): bool
{
if (null === $this->isInstalledByComposer) {
$this->isInstalledByComposer = !$this->isInstalledAsPhar() && file_exists($this->getComposerInstalledFile());
}

return $this->isInstalledByComposer;
}





public function isRunInsideDocker(): bool
{
return is_file('/.dockerenv') && str_starts_with(__FILE__, '/fixer/');
}

public function getPharDownloadUri(string $version): string
{
return \sprintf(
'https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases/download/%s/php-cs-fixer.phar',
$version
);
}

private function getComposerInstalledFile(): string
{
return __DIR__.'/../../../composer/installed.json';
}
}
