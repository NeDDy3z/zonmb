<?php

declare(strict_types=1);











namespace PhpCsFixer;






final class FixerNameValidator
{
public function isValid(string $name, bool $isCustom): bool
{
if (!$isCustom) {
return Preg::match('/^[a-z][a-z0-9_]*$/', $name);
}

return Preg::match('/^[A-Z][a-zA-Z0-9]*\/[a-z][a-z0-9_]*$/', $name);
}
}
