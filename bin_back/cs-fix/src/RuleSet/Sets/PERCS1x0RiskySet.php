<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDescription;








final class PERCS1x0RiskySet extends AbstractRuleSetDescription
{
public function getName(): string
{
return '@PER-CS1.0:risky';
}

public function getRules(): array
{
return [
'@PSR12:risky' => true,
];
}

public function getDescription(): string
{
return 'Rules that follow `PER Coding Style 1.0 <https://www.php-fig.org/per/coding-style/>`_.';
}
}
