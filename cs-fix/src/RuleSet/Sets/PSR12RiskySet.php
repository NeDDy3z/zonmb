<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDescription;




final class PSR12RiskySet extends AbstractRuleSetDescription
{
public function getRules(): array
{
return [
'no_trailing_whitespace_in_string' => true,
'no_unreachable_default_argument_value' => true,
];
}

public function getDescription(): string
{
return 'Rules that follow `PSR-12 <https://www.php-fig.org/psr/psr-12/>`_ standard.';
}
}
