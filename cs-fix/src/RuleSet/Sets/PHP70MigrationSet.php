<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP70MigrationSet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP54Migration' => true,
'ternary_to_null_coalescing' => true,
];
}
}
