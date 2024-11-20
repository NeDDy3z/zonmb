<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet;

use Symfony\Component\Finder\Finder;






final class RuleSets
{



private static $setDefinitions;




public static function getSetDefinitions(): array
{
if (null === self::$setDefinitions) {
self::$setDefinitions = [];

foreach (Finder::create()->files()->in(__DIR__.'/Sets') as $file) {
$class = 'PhpCsFixer\RuleSet\Sets\\'.$file->getBasename('.php');
$set = new $class();

self::$setDefinitions[$set->getName()] = $set;
}

uksort(self::$setDefinitions, static fn (string $x, string $y): int => strnatcmp($x, $y));
}

return self::$setDefinitions;
}




public static function getSetDefinitionNames(): array
{
return array_keys(self::getSetDefinitions());
}

public static function getSetDefinition(string $name): RuleSetDescriptionInterface
{
$definitions = self::getSetDefinitions();

if (!isset($definitions[$name])) {
throw new \InvalidArgumentException(\sprintf('Set "%s" does not exist.', $name));
}

return $definitions[$name];
}
}
