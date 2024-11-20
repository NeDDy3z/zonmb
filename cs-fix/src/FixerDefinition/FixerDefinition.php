<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerDefinition;




final class FixerDefinition implements FixerDefinitionInterface
{
private string $summary;




private array $codeSamples;




private ?string $description;




private ?string $riskyDescription;





public function __construct(
string $summary,
array $codeSamples,
?string $description = null,
?string $riskyDescription = null
) {
$this->summary = $summary;
$this->codeSamples = $codeSamples;
$this->description = $description;
$this->riskyDescription = $riskyDescription;
}

public function getSummary(): string
{
return $this->summary;
}

public function getDescription(): ?string
{
return $this->description;
}

public function getRiskyDescription(): ?string
{
return $this->riskyDescription;
}

public function getCodeSamples(): array
{
return $this->codeSamples;
}
}
