<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDescription;
use PhpCsFixer\RuleSet\DeprecatedRuleSetDescriptionInterface;










final class PERRiskySet extends AbstractRuleSetDescription implements DeprecatedRuleSetDescriptionInterface
{
public function getName(): string
{
return '@PER:risky';
}

public function getRules(): array
{
return [
'@PER-CS:risky' => true,
];
}

public function getDescription(): string
{
return 'Alias for the newest PER-CS risky rules. It is recommended you use ``@PER-CS2.0:risky`` instead if you want to stick with stable ruleset.';
}

public function getSuccessorsNames(): array
{
return ['@PER-CS:risky'];
}
}
