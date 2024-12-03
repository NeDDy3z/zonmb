<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerDefinition;




final class VersionSpecificCodeSample implements VersionSpecificCodeSampleInterface
{
private CodeSampleInterface $codeSample;

private VersionSpecificationInterface $versionSpecification;




public function __construct(
string $code,
VersionSpecificationInterface $versionSpecification,
?array $configuration = null
) {
$this->codeSample = new CodeSample($code, $configuration);
$this->versionSpecification = $versionSpecification;
}

public function getCode(): string
{
return $this->codeSample->getCode();
}

public function getConfiguration(): ?array
{
return $this->codeSample->getConfiguration();
}

public function isSuitableFor(int $version): bool
{
return $this->versionSpecification->isSatisfiedBy($version);
}
}
