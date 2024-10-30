<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet;








interface RuleSetInterface
{



public function __construct(array $set = []);






public function getRuleConfiguration(string $rule): ?array;






public function getRules(): array;




public function hasRule(string $rule): bool;
}
