<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet;




interface RuleSetDescriptionInterface
{
public function getDescription(): string;

public function getName(): string;






public function getRules(): array;

public function isRisky(): bool;
}
