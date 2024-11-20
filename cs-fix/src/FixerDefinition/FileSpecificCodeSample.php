<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerDefinition;






final class FileSpecificCodeSample implements FileSpecificCodeSampleInterface
{
private CodeSampleInterface $codeSample;

private \SplFileInfo $splFileInfo;




public function __construct(
string $code,
\SplFileInfo $splFileInfo,
?array $configuration = null
) {
$this->codeSample = new CodeSample($code, $configuration);
$this->splFileInfo = $splFileInfo;
}

public function getCode(): string
{
return $this->codeSample->getCode();
}

public function getConfiguration(): ?array
{
return $this->codeSample->getConfiguration();
}

public function getSplFileInfo(): \SplFileInfo
{
return $this->splFileInfo;
}
}
