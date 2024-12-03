<?php

declare(strict_types=1);











namespace PhpCsFixer;




interface PharCheckerInterface
{



public function checkFileValidity(string $filename): ?string;
}
