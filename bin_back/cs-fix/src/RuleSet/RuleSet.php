<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet;

use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Utils;








final class RuleSet implements RuleSetInterface
{








private array $rules;

public function __construct(array $set = [])
{
foreach ($set as $name => $value) {
if ('' === $name) {
throw new \InvalidArgumentException('Rule/set name must not be empty.');
}

if (\is_int($name)) {
throw new \InvalidArgumentException(\sprintf('Missing value for "%s" rule/set.', $value));
}

if (!\is_bool($value) && !\is_array($value)) {
$message = str_starts_with($name, '@') ? 'Set must be enabled (true) or disabled (false). Other values are not allowed.' : 'Rule must be enabled (true), disabled (false) or configured (non-empty, assoc array). Other values are not allowed.';

if (null === $value) {
$message .= ' To disable the '.(str_starts_with($name, '@') ? 'set' : 'rule').', use "FALSE" instead of "NULL".';
}

throw new InvalidFixerConfigurationException($name, $message);
}
}

$this->resolveSet($set);
}

public function hasRule(string $rule): bool
{
return \array_key_exists($rule, $this->rules);
}

public function getRuleConfiguration(string $rule): ?array
{
if (!$this->hasRule($rule)) {
throw new \InvalidArgumentException(\sprintf('Rule "%s" is not in the set.', $rule));
}

if (true === $this->rules[$rule]) {
return null;
}

return $this->rules[$rule];
}

public function getRules(): array
{
return $this->rules;
}






private function resolveSet(array $rules): void
{
$resolvedRules = [];


foreach ($rules as $name => $value) {
if (str_starts_with($name, '@')) {
if (!\is_bool($value)) {
throw new \UnexpectedValueException(\sprintf('Nested rule set "%s" configuration must be a boolean.', $name));
}

$set = $this->resolveSubset($name, $value);
$resolvedRules = array_merge($resolvedRules, $set);
} else {
$resolvedRules[$name] = $value;
}
}


$resolvedRules = array_filter(
$resolvedRules,
static fn ($value): bool => false !== $value
);

$this->rules = $resolvedRules;
}









private function resolveSubset(string $setName, bool $setValue): array
{
$ruleSet = RuleSets::getSetDefinition($setName);

if ($ruleSet instanceof DeprecatedRuleSetDescriptionInterface) {
$messageEnd = [] === $ruleSet->getSuccessorsNames()
? 'No replacement available'
: \sprintf('Use %s instead', Utils::naturalLanguageJoin($ruleSet->getSuccessorsNames()));

Utils::triggerDeprecation(new \RuntimeException("Rule set \"{$setName}\" is deprecated. {$messageEnd}."));
}

$rules = $ruleSet->getRules();

foreach ($rules as $name => $value) {
if (str_starts_with($name, '@')) {
$set = $this->resolveSubset($name, $setValue);
unset($rules[$name]);
$rules = array_merge($rules, $set);
} elseif (!$setValue) {
$rules[$name] = false;
} else {
$rules[$name] = $value;
}
}

return $rules;
}
}
