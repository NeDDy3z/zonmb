<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerDefinition;




interface VersionSpecificCodeSampleInterface extends CodeSampleInterface
{
public function isSuitableFor(int $version): bool;
}
