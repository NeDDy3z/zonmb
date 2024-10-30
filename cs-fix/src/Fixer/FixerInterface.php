<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer;

use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;





interface FixerInterface
{









public function isCandidate(Tokens $tokens): bool;






public function isRisky(): bool;







public function fix(\SplFileInfo $file, Tokens $tokens): void;




public function getDefinition(): FixerDefinitionInterface;








public function getName(): string;






public function getPriority(): int;






public function supports(\SplFileInfo $file): bool;
}
