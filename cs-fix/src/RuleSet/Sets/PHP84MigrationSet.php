<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP84MigrationSet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP83Migration' => true,
'nullable_type_declaration_for_default_null_value' => true,
];
}
}
