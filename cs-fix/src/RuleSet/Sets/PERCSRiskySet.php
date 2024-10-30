<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDescription;




final class PERCSRiskySet extends AbstractRuleSetDescription
{
public function getName(): string
{
return '@PER-CS:risky';
}

public function getRules(): array
{
return [
'@PER-CS2.0:risky' => true,
];
}

public function getDescription(): string
{
return 'Alias for the latest revision of PER-CS risky rules. Use it if you always want to be in sync with newest PER-CS standard.';
}
}
