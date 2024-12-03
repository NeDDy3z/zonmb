<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP80MigrationSet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP74Migration' => true,
'clean_namespace' => true,
'no_unset_cast' => true,
];
}
}
