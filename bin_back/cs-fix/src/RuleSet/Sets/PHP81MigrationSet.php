<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP81MigrationSet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP80Migration' => true,
'octal_notation' => true,
];
}
}
