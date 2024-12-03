<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHPUnit91MigrationRiskySet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHPUnit84Migration:risky' => true,
'php_unit_assert_new_names' => true,
];
}
}
