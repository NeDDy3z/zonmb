<?php










namespace Symfony\Component\OptionsResolver;

use Symfony\Component\OptionsResolver\Exception\AccessException;

final class OptionConfigurator
{
private $name;
private $resolver;

public function __construct(string $name, OptionsResolver $resolver)
{
$this->name = $name;
$this->resolver = $resolver;
$this->resolver->setDefined($name);
}








public function allowedTypes(string ...$types): self
{
$this->resolver->setAllowedTypes($this->name, $types);

return $this;
}










public function allowedValues(...$values): self
{
$this->resolver->setAllowedValues($this->name, $values);

return $this;
}










public function default($value): self
{
$this->resolver->setDefault($this->name, $value);

return $this;
}




public function define(string $option): self
{
return $this->resolver->define($option);
}










public function deprecated(string $package, string $version, $message = 'The option "%name%" is deprecated.'): self
{
$this->resolver->setDeprecated($this->name, $package, $version, $message);

return $this;
}








public function normalize(\Closure $normalizer): self
{
$this->resolver->setNormalizer($this->name, $normalizer);

return $this;
}








public function required(): self
{
$this->resolver->setRequired($this->name);

return $this;
}








public function info(string $info): self
{
$this->resolver->setInfo($this->name, $info);

return $this;
}
}
