<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerConfiguration;






final class AliasedFixerOption implements FixerOptionInterface
{
private FixerOptionInterface $fixerOption;

private string $alias;

public function __construct(FixerOptionInterface $fixerOption, string $alias)
{
$this->fixerOption = $fixerOption;
$this->alias = $alias;
}

public function getAlias(): string
{
return $this->alias;
}

public function getName(): string
{
return $this->fixerOption->getName();
}

public function getDescription(): string
{
return $this->fixerOption->getDescription();
}

public function hasDefault(): bool
{
return $this->fixerOption->hasDefault();
}

public function getDefault()
{
return $this->fixerOption->getDefault();
}

public function getAllowedTypes(): ?array
{
return $this->fixerOption->getAllowedTypes();
}

public function getAllowedValues(): ?array
{
return $this->fixerOption->getAllowedValues();
}

public function getNormalizer(): ?\Closure
{
return $this->fixerOption->getNormalizer();
}
}
