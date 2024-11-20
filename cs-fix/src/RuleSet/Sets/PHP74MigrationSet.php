<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP74MigrationSet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP73Migration' => true,
'assign_null_coalescing_to_coalesce_equal' => true,
'normalize_index_brace' => true,
'short_scalar_cast' => true,
];
}
}
