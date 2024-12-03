<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer;













interface TransformerInterface
{





public function getCustomTokens(): array;








public function getName(): string;






public function getPriority(): int;









public function getRequiredPhpVersionId(): int;




public function process(Tokens $tokens, Token $token, int $index): void;
}
