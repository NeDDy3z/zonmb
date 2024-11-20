<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion;
use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHPUnit30MigrationRiskySet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'php_unit_dedicate_assert' => [
'target' => PhpUnitTargetVersion::VERSION_3_0,
],
];
}
}
