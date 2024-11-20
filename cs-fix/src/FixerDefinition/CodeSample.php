<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerDefinition;




final class CodeSample implements CodeSampleInterface
{
private string $code;




private ?array $configuration;




public function __construct(string $code, ?array $configuration = null)
{
$this->code = $code;
$this->configuration = $configuration;
}

public function getCode(): string
{
return $this->code;
}

public function getConfiguration(): ?array
{
return $this->configuration;
}
}
