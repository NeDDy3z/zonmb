<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerConfiguration;






final class AliasedFixerOptionBuilder
{
private FixerOptionBuilder $optionBuilder;

private string $alias;

public function __construct(FixerOptionBuilder $optionBuilder, string $alias)
{
$this->optionBuilder = $optionBuilder;
$this->alias = $alias;
}




public function setDefault($default): self
{
$this->optionBuilder->setDefault($default);

return $this;
}




public function setAllowedTypes(array $allowedTypes): self
{
$this->optionBuilder->setAllowedTypes($allowedTypes);

return $this;
}




public function setAllowedValues(array $allowedValues): self
{
$this->optionBuilder->setAllowedValues($allowedValues);

return $this;
}

public function setNormalizer(\Closure $normalizer): self
{
$this->optionBuilder->setNormalizer($normalizer);

return $this;
}

public function getOption(): AliasedFixerOption
{
return new AliasedFixerOption(
$this->optionBuilder->getOption(),
$this->alias
);
}
}
