<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer;




interface DeprecatedFixerInterface extends FixerInterface
{





public function getSuccessorsNames(): array;
}
