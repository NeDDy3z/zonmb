<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\Fixer\FixerInterface;





interface ConfigInterface
{





public function getCacheFile(): ?string;






public function getCustomFixers(): array;






public function getFinder(): iterable;

public function getFormat(): string;




public function getHideProgress(): bool;

public function getIndent(): string;

public function getLineEnding(): string;








public function getName(): string;




public function getPhpExecutable(): ?string;




public function getRiskyAllowed(): bool;








public function getRules(): array;




public function getUsingCache(): bool;








public function registerCustomFixers(iterable $fixers): self;




public function setCacheFile(string $cacheFile): self;




public function setFinder(iterable $finder): self;

public function setFormat(string $format): self;

public function setHideProgress(bool $hideProgress): self;

public function setIndent(string $indent): self;

public function setLineEnding(string $lineEnding): self;




public function setPhpExecutable(?string $phpExecutable): self;




public function setRiskyAllowed(bool $isRiskyAllowed): self;











public function setRules(array $rules): self;

public function setUsingCache(bool $usingCache): self;
}
