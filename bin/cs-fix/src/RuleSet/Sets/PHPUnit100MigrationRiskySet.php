<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHPUnit100MigrationRiskySet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHPUnit91Migration:risky' => true,
'php_unit_data_provider_static' => ['force' => true],
];
}
}
