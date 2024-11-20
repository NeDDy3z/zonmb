<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerConfiguration;

interface FixerOptionInterface
{
public function getName(): string;

public function getDescription(): string;

public function hasDefault(): bool;






public function getDefault();




public function getAllowedTypes(): ?array;




public function getAllowedValues(): ?array;

public function getNormalizer(): ?\Closure;
}
