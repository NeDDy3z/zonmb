<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion;
use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHPUnit55MigrationRiskySet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHPUnit54Migration:risky' => true,
'php_unit_mock' => [
'target' => PhpUnitTargetVersion::VERSION_5_5,
],
];
}
}