<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet;

use PhpCsFixer\Preg;




abstract class AbstractMigrationSetDescription extends AbstractRuleSetDescription
{
public function getDescription(): string
{
$name = $this->getName();

if (Preg::match('#^@PHPUnit(\d+)(\d)Migration.*$#', $name, $matches)) {
return \sprintf('Rules to improve tests code for PHPUnit %d.%d compatibility.', $matches[1], $matches[2]);
}

if (Preg::match('#^@PHP([\d]{2})Migration.*$#', $name, $matches)) {
return \sprintf('Rules to improve code for PHP %d.%d compatibility.', $matches[1][0], $matches[1][1]);
}

throw new \RuntimeException(\sprintf('Cannot generate description for "%s" "%s".', static::class, $name));
}
}
