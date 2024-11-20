<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion;
use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHPUnit56MigrationRiskySet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHPUnit55Migration:risky' => true,
'php_unit_dedicate_assert' => [
'target' => PhpUnitTargetVersion::VERSION_5_6,
],
'php_unit_expectation' => [
'target' => PhpUnitTargetVersion::VERSION_5_6,
],
];
}
}
