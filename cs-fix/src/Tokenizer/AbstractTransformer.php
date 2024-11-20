<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer;

use PhpCsFixer\Utils;






abstract class AbstractTransformer implements TransformerInterface
{
public function getName(): string
{
$nameParts = explode('\\', static::class);
$name = substr(end($nameParts), 0, -\strlen('Transformer'));

return Utils::camelCaseToUnderscore($name);
}

public function getPriority(): int
{
return 0;
}

abstract public function getCustomTokens(): array;
}
