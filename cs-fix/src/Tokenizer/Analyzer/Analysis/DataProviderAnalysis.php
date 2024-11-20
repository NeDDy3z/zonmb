<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;

use PhpCsFixer\Console\Application;
use PhpCsFixer\Utils;

final class DataProviderAnalysis
{
private string $name;

private int $nameIndex;


private array $usageIndices;




public function __construct(string $name, int $nameIndex, array $usageIndices)
{
if (!array_is_list($usageIndices)) {
Utils::triggerDeprecation(new \InvalidArgumentException(\sprintf(
'Parameter "usageIndices" should be a list. This will be enforced in version %d.0.',
Application::getMajorVersion() + 1
)));
}

$this->name = $name;
$this->nameIndex = $nameIndex;
$this->usageIndices = $usageIndices;
}

public function getName(): string
{
return $this->name;
}

public function getNameIndex(): int
{
return $this->nameIndex;
}




public function getUsageIndices(): array
{
return $this->usageIndices;
}
}
