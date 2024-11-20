<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerConfiguration;

final class FixerOption implements FixerOptionInterface
{
private string $name;

private string $description;

private bool $isRequired;




private $default;




private $allowedTypes;




private $allowedValues;




private $normalizer;






public function __construct(
string $name,
string $description,
bool $isRequired = true,
$default = null,
?array $allowedTypes = null,
?array $allowedValues = null,
?\Closure $normalizer = null
) {
if ($isRequired && null !== $default) {
throw new \LogicException('Required options cannot have a default value.');
}

if (null !== $allowedValues) {
foreach ($allowedValues as &$allowedValue) {
if ($allowedValue instanceof \Closure) {
$allowedValue = $this->unbind($allowedValue);
}
}
}

$this->name = $name;
$this->description = $description;
$this->isRequired = $isRequired;
$this->default = $default;
$this->allowedTypes = $allowedTypes;
$this->allowedValues = $allowedValues;

if (null !== $normalizer) {
$this->normalizer = $this->unbind($normalizer);
}
}

public function getName(): string
{
return $this->name;
}

public function getDescription(): string
{
return $this->description;
}

public function hasDefault(): bool
{
return !$this->isRequired;
}

public function getDefault()
{
if (!$this->hasDefault()) {
throw new \LogicException('No default value defined.');
}

return $this->default;
}

public function getAllowedTypes(): ?array
{
return $this->allowedTypes;
}

public function getAllowedValues(): ?array
{
return $this->allowedValues;
}

public function getNormalizer(): ?\Closure
{
return $this->normalizer;
}
















private function unbind(\Closure $closure): \Closure
{
return $closure->bindTo(null);
}
}
