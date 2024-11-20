<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDescription;




final class PSR1Set extends AbstractRuleSetDescription
{
public function getRules(): array
{
return [
'encoding' => true,
'full_opening_tag' => true,
];
}

public function getDescription(): string
{
return 'Rules that follow `PSR-1 <https://www.php-fig.org/psr/psr-1/>`_ standard.';
}
}
