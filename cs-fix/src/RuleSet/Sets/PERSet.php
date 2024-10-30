<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDescription;
use PhpCsFixer\RuleSet\DeprecatedRuleSetDescriptionInterface;










final class PERSet extends AbstractRuleSetDescription implements DeprecatedRuleSetDescriptionInterface
{
public function getRules(): array
{
return [
'@PER-CS' => true,
];
}

public function getDescription(): string
{
return 'Alias for the newest PER-CS rules. It is recommended you use ``@PER-CS2.0`` instead if you want to stick with stable ruleset.';
}

public function getSuccessorsNames(): array
{
return ['@PER-CS'];
}
}
