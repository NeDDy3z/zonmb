<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP56MigrationRiskySet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'pow_to_exponentiation' => true,
];
}
}
