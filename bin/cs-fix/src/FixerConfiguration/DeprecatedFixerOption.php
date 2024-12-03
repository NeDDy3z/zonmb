<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerConfiguration;

final class DeprecatedFixerOption implements DeprecatedFixerOptionInterface
{
private FixerOptionInterface $option;

private string $deprecationMessage;

public function __construct(FixerOptionInterface $option, string $deprecationMessage)
{
$this->option = $option;
$this->deprecationMessage = $deprecationMessage;
}

public function getName(): string
{
return $this->option->getName();
}

public function getDescription(): string
{
return $this->option->getDescription();
}

public function hasDefault(): bool
{
return $this->option->hasDefault();
}

public function getDefault()
{
return $this->option->getDefault();
}

public function getAllowedTypes(): ?array
{
return $this->option->getAllowedTypes();
}

public function getAllowedValues(): ?array
{
return $this->option->getAllowedValues();
}

public function getNormalizer(): ?\Closure
{
return $this->option->getNormalizer();
}

public function getDeprecationMessage(): string
{
return $this->deprecationMessage;
}
}
