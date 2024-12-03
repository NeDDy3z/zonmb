<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP54MigrationSet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'array_syntax' => true,
];
}
}
