<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP70MigrationRiskySet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP56Migration:risky' => true,
'combine_nested_dirname' => true,
'declare_strict_types' => true,
'non_printable_character' => true,
'random_api_migration' => [
'replacements' => [
'mt_rand' => 'random_int',
'rand' => 'random_int',
],
],
];
}
}
