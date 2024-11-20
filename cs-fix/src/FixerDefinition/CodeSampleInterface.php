<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerDefinition;




interface CodeSampleInterface
{
public function getCode(): string;




public function getConfiguration(): ?array;
}
