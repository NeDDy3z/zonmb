<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDescription;








final class PERCS2x0RiskySet extends AbstractRuleSetDescription
{
public function getName(): string
{
return '@PER-CS2.0:risky';
}

public function getRules(): array
{
return [
'@PER-CS1.0:risky' => true,
];
}

public function getDescription(): string
{
return 'Rules that follow `PER Coding Style 2.0 <https://www.php-fig.org/per/coding-style/>`_.';
}
}
