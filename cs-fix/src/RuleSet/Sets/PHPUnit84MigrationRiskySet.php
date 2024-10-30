<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion;
use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHPUnit84MigrationRiskySet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHPUnit60Migration:risky' => true,
'@PHPUnit75Migration:risky' => true,
'php_unit_expectation' => [
'target' => PhpUnitTargetVersion::VERSION_8_4,
],
];
}
}
