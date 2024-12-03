<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerDefinition;






interface FileSpecificCodeSampleInterface extends CodeSampleInterface
{
public function getSplFileInfo(): \SplFileInfo;
}
