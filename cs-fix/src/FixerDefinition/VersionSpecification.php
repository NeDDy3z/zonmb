<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerDefinition;




final class VersionSpecification implements VersionSpecificationInterface
{



private ?int $minimum;




private ?int $maximum;







public function __construct(?int $minimum = null, ?int $maximum = null)
{
if (null === $minimum && null === $maximum) {
throw new \InvalidArgumentException('Minimum or maximum need to be specified.');
}

if (null !== $minimum && 1 > $minimum) {
throw new \InvalidArgumentException('Minimum needs to be either null or an integer greater than 0.');
}

if (null !== $maximum) {
if (1 > $maximum) {
throw new \InvalidArgumentException('Maximum needs to be either null or an integer greater than 0.');
}

if (null !== $minimum && $maximum < $minimum) {
throw new \InvalidArgumentException('Maximum should not be lower than the minimum.');
}
}

$this->minimum = $minimum;
$this->maximum = $maximum;
}

public function isSatisfiedBy(int $version): bool
{
if (null !== $this->minimum && $version < $this->minimum) {
return false;
}

if (null !== $this->maximum && $version > $this->maximum) {
return false;
}

return true;
}
}
