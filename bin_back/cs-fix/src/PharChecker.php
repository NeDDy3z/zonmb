<?php

declare(strict_types=1);











namespace PhpCsFixer;




final class PharChecker implements PharCheckerInterface
{
public function checkFileValidity(string $filename): ?string
{
try {
$phar = new \Phar($filename);

unset($phar);
} catch (\Exception $e) {
if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
throw $e;
}

return 'Failed to create Phar instance. '.$e->getMessage();
}

return null;
}
}
