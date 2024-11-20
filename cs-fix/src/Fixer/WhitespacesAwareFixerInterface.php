<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer;

use PhpCsFixer\WhitespacesFixerConfig;




interface WhitespacesAwareFixerInterface extends FixerInterface
{
public function setWhitespacesConfig(WhitespacesFixerConfig $config): void;
}
