<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\ConfigurationException\RequiredFixerConfigurationException;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\Tokenizer\Tokens;






abstract class AbstractFixer implements FixerInterface
{



protected $whitespacesConfig;

public function __construct()
{
if ($this instanceof ConfigurableFixerInterface) {
try {
$this->configure([]);
} catch (RequiredFixerConfigurationException $e) {

}
}

if ($this instanceof WhitespacesAwareFixerInterface) {
$this->whitespacesConfig = $this->getDefaultWhitespacesFixerConfig();
}
}

final public function fix(\SplFileInfo $file, Tokens $tokens): void
{
if ($this instanceof ConfigurableFixerInterface && property_exists($this, 'configuration') && null === $this->configuration) {
throw new RequiredFixerConfigurationException($this->getName(), 'Configuration is required.');
}

if (0 < $tokens->count() && $this->isCandidate($tokens) && $this->supports($file)) {
$this->applyFix($file, $tokens);
}
}

public function isRisky(): bool
{
return false;
}

public function getName(): string
{
$nameParts = explode('\\', static::class);
$name = substr(end($nameParts), 0, -\strlen('Fixer'));

return Utils::camelCaseToUnderscore($name);
}

public function getPriority(): int
{
return 0;
}

public function supports(\SplFileInfo $file): bool
{
return true;
}

public function setWhitespacesConfig(WhitespacesFixerConfig $config): void
{
if (!$this instanceof WhitespacesAwareFixerInterface) {
throw new \LogicException('Cannot run method for class not implementing "PhpCsFixer\Fixer\WhitespacesAwareFixerInterface".');
}

$this->whitespacesConfig = $config;
}

abstract protected function applyFix(\SplFileInfo $file, Tokens $tokens): void;

private function getDefaultWhitespacesFixerConfig(): WhitespacesFixerConfig
{
static $defaultWhitespacesFixerConfig = null;

if (null === $defaultWhitespacesFixerConfig) {
$defaultWhitespacesFixerConfig = new WhitespacesFixerConfig('    ', "\n");
}

return $defaultWhitespacesFixerConfig;
}
}
