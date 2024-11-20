<?php

declare(strict_types=1);











namespace PhpCsFixer\Console;

use PhpCsFixer\ToolInfo;
use PhpCsFixer\ToolInfoInterface;






final class WarningsDetector
{
private ToolInfoInterface $toolInfo;




private array $warnings = [];

public function __construct(ToolInfoInterface $toolInfo)
{
$this->toolInfo = $toolInfo;
}

public function detectOldMajor(): void
{





}

public function detectOldVendor(): void
{
if ($this->toolInfo->isInstalledByComposer()) {
$details = $this->toolInfo->getComposerInstallationDetails();
if (ToolInfo::COMPOSER_LEGACY_PACKAGE_NAME === $details['name']) {
$this->warnings[] = \sprintf(
'You are running PHP CS Fixer installed with old vendor `%s`. Please update to `%s`.',
ToolInfo::COMPOSER_LEGACY_PACKAGE_NAME,
ToolInfo::COMPOSER_PACKAGE_NAME
);
}
}
}




public function getWarnings(): array
{
if (0 === \count($this->warnings)) {
return [];
}

return array_values(array_unique(array_merge(
$this->warnings,
['If you need help while solving warnings, ask at https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/discussions/, we will help you!']
)));
}
}
