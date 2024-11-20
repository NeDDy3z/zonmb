<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDescription;




final class PERCSSet extends AbstractRuleSetDescription
{
public function getName(): string
{
return '@PER-CS';
}

public function getRules(): array
{
return [
'@PER-CS2.0' => true,
];
}

public function getDescription(): string
{
return 'Alias for the latest revision of PER-CS rules. Use it if you always want to be in sync with newest PER-CS standard.';
}
}
