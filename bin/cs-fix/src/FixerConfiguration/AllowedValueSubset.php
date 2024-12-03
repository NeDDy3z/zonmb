<?php

declare(strict_types=1);











namespace PhpCsFixer\FixerConfiguration;




final class AllowedValueSubset
{



private array $allowedValues;




public function __construct(array $allowedValues)
{
$this->allowedValues = $allowedValues;
sort($this->allowedValues, SORT_FLAG_CASE | SORT_STRING);
}






public function __invoke($values): bool
{
if (!\is_array($values)) {
return false;
}

foreach ($values as $value) {
if (!\in_array($value, $this->allowedValues, true)) {
return false;
}
}

return true;
}




public function getAllowedValues(): array
{
return $this->allowedValues;
}
}
