<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion;
use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHPUnit60MigrationRiskySet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHPUnit57Migration:risky' => true,
'php_unit_namespaced' => [
'target' => PhpUnitTargetVersion::VERSION_6_0,
],
];
}
}
