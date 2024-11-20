<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerConfiguration;

interface DeprecatedFixerOptionInterface extends FixerOptionInterface
{
public function getDeprecationMessage(): string;
}
