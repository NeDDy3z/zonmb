<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerDefinition;




interface VersionSpecificationInterface
{
public function isSatisfiedBy(int $version): bool;
}
