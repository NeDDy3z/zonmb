<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP83MigrationSet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP82Migration' => true,
];
}
}
