<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP71MigrationSet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP70Migration' => true,
'list_syntax' => true,
'visibility_required' => true,
];
}
}
