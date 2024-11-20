<?php

declare(strict_types=1);











namespace PhpCsFixer\ConfigurationException;

use PhpCsFixer\Console\Command\FixCommandExitStatusCalculator;








class InvalidFixerConfigurationException extends InvalidConfigurationException
{
private string $fixerName;

public function __construct(string $fixerName, string $message, ?\Throwable $previous = null)
{
parent::__construct(
\sprintf('[%s] %s', $fixerName, $message),
FixCommandExitStatusCalculator::EXIT_STATUS_FLAG_HAS_INVALID_FIXER_CONFIG,
$previous
);

$this->fixerName = $fixerName;
}

public function getFixerName(): string
{
return $this->fixerName;
}
}
