<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerConfiguration;

final class FixerOptionBuilder
{
private string $name;

private string $description;




private $default;

private bool $isRequired = true;




private $allowedTypes;




private $allowedValues;




private $normalizer;




private $deprecationMessage;

public function __construct(string $name, string $description)
{
$this->name = $name;
$this->description = $description;
}






public function setDefault($default): self
{
$this->default = $default;
$this->isRequired = false;

return $this;
}






public function setAllowedTypes(array $allowedTypes): self
{
$this->allowedTypes = $allowedTypes;

return $this;
}






public function setAllowedValues(array $allowedValues): self
{
$this->allowedValues = $allowedValues;

return $this;
}




public function setNormalizer(\Closure $normalizer): self
{
$this->normalizer = $normalizer;

return $this;
}




public function setDeprecationMessage(?string $deprecationMessage): self
{
$this->deprecationMessage = $deprecationMessage;

return $this;
}

public function getOption(): FixerOptionInterface
{
$option = new FixerOption(
$this->name,
$this->description,
$this->isRequired,
$this->default,
$this->allowedTypes,
$this->allowedValues,
$this->normalizer
);

if (null !== $this->deprecationMessage) {
$option = new DeprecatedFixerOption($option, $this->deprecationMessage);
}

return $option;
}
}
