<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer;

use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\ConfigurationException\InvalidForEnvFixerConfigurationException;
use PhpCsFixer\ConfigurationException\RequiredFixerConfigurationException;
use PhpCsFixer\Console\Application;
use PhpCsFixer\FixerConfiguration\DeprecatedFixerOption;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\InvalidOptionsForEnvException;
use PhpCsFixer\Utils;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
@template
@template




*/
trait ConfigurableFixerTrait
{



protected $configuration;




private $configurationDefinition;




final public function configure(array $configuration): void
{
$this->configurePreNormalisation($configuration);

foreach ($this->getConfigurationDefinition()->getOptions() as $option) {
if (!$option instanceof DeprecatedFixerOption) {
continue;
}

$name = $option->getName();
if (\array_key_exists($name, $configuration)) {
Utils::triggerDeprecation(new \InvalidArgumentException(\sprintf(
'Option "%s" for rule "%s" is deprecated and will be removed in version %d.0. %s',
$name,
$this->getName(),
Application::getMajorVersion() + 1,
str_replace('`', '"', $option->getDeprecationMessage())
)));
}
}

try {
$this->configuration = $this->getConfigurationDefinition()->resolve($configuration); 
} catch (MissingOptionsException $exception) {
throw new RequiredFixerConfigurationException(
$this->getName(),
\sprintf('Missing required configuration: %s', $exception->getMessage()),
$exception
);
} catch (InvalidOptionsForEnvException $exception) {
throw new InvalidForEnvFixerConfigurationException(
$this->getName(),
\sprintf('Invalid configuration for env: %s', $exception->getMessage()),
$exception
);
} catch (ExceptionInterface $exception) {
throw new InvalidFixerConfigurationException(
$this->getName(),
\sprintf('Invalid configuration: %s', $exception->getMessage()),
$exception
);
}

$this->configurePostNormalisation();
}

final public function getConfigurationDefinition(): FixerConfigurationResolverInterface
{
if (null === $this->configurationDefinition) {
$this->configurationDefinition = $this->createConfigurationDefinition();
}

return $this->configurationDefinition;
}

abstract public function getName(): string;






protected function configurePreNormalisation(array &$configuration): void
{

}




protected function configurePostNormalisation(): void
{

}

abstract protected function createConfigurationDefinition(): FixerConfigurationResolverInterface;
}
