<?php

declare(strict_types=1);











namespace PhpCsFixer\ConfigurationException;

use PhpCsFixer\Console\Command\FixCommandExitStatusCalculator;








class InvalidConfigurationException extends \InvalidArgumentException
{
public function __construct(string $message, ?int $code = null, ?\Throwable $previous = null)
{
parent::__construct(
$message,
$code ?? FixCommandExitStatusCalculator::EXIT_STATUS_FLAG_HAS_INVALID_CONFIG,
$previous
);
}
}
