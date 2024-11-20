<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP80MigrationRiskySet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP74Migration:risky' => true,
'get_class_to_class_keyword' => true,
'modernize_strpos' => true,
'no_alias_functions' => [
'sets' => [
'@all',
],
],
'no_php4_constructor' => true,
'no_unneeded_final_method' => true, 
'no_unreachable_default_argument_value' => true,
];
}
}
