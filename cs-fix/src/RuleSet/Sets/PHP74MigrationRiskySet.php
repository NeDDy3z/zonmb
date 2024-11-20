<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP74MigrationRiskySet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP71Migration:risky' => true,
'implode_call' => true,
'no_alias_functions' => true,
'use_arrow_functions' => true,
];
}
}
