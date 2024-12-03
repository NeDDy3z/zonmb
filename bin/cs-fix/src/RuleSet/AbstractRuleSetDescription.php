<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet;




abstract class AbstractRuleSetDescription implements RuleSetDescriptionInterface
{
public function __construct() {}

public function getName(): string
{
$name = substr(static::class, 1 + strrpos(static::class, '\\'), -3);

return '@'.str_replace('Risky', ':risky', $name);
}

public function isRisky(): bool
{
return str_contains(static::class, 'Risky');
}
}
