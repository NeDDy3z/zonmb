<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\Tokenizer\Tokens;






abstract class AbstractProxyFixer extends AbstractFixer
{



protected array $proxyFixers = [];

public function __construct()
{
foreach (Utils::sortFixers($this->createProxyFixers()) as $proxyFixer) {
$this->proxyFixers[$proxyFixer->getName()] = $proxyFixer;
}

parent::__construct();
}

public function isCandidate(Tokens $tokens): bool
{
foreach ($this->proxyFixers as $fixer) {
if ($fixer->isCandidate($tokens)) {
return true;
}
}

return false;
}

public function isRisky(): bool
{
foreach ($this->proxyFixers as $fixer) {
if ($fixer->isRisky()) {
return true;
}
}

return false;
}

public function getPriority(): int
{
if (\count($this->proxyFixers) > 1) {
throw new \LogicException('You need to override this method to provide the priority of combined fixers.');
}

return reset($this->proxyFixers)->getPriority();
}

public function supports(\SplFileInfo $file): bool
{
foreach ($this->proxyFixers as $fixer) {
if ($fixer->supports($file)) {
return true;
}
}

return false;
}

public function setWhitespacesConfig(WhitespacesFixerConfig $config): void
{
parent::setWhitespacesConfig($config);

foreach ($this->proxyFixers as $fixer) {
if ($fixer instanceof WhitespacesAwareFixerInterface) {
$fixer->setWhitespacesConfig($config);
}
}
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($this->proxyFixers as $fixer) {
$fixer->fix($file, $tokens);
}
}




abstract protected function createProxyFixers(): array;
}
