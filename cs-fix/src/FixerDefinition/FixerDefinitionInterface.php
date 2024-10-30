<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerDefinition;




interface FixerDefinitionInterface
{
public function getSummary(): string;

public function getDescription(): ?string;




public function getRiskyDescription(): ?string;






public function getCodeSamples(): array;
}
