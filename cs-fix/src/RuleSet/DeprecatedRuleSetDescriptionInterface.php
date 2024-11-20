<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet;




interface DeprecatedRuleSetDescriptionInterface extends RuleSetDescriptionInterface
{





public function getSuccessorsNames(): array;
}
