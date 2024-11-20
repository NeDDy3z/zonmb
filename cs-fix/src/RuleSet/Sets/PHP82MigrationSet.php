<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP82MigrationSet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP81Migration' => true,
'simple_to_complex_string_variable' => true,
];
}
}
