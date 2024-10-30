<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Command;

use PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use PhpCsFixer\FixerConfiguration\FixerOptionInterface;
use PhpCsFixer\Utils;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\HelpCommand as BaseHelpCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;







#[AsCommand(name: 'help')]
final class HelpCommand extends BaseHelpCommand
{

protected static $defaultName = 'help';






public static function getDisplayableAllowedValues(FixerOptionInterface $option): ?array
{
$allowed = $option->getAllowedValues();

if (null !== $allowed) {
$allowed = array_filter($allowed, static fn ($value): bool => !$value instanceof \Closure);

usort($allowed, static function ($valueA, $valueB): int {
if ($valueA instanceof AllowedValueSubset) {
return -1;
}

if ($valueB instanceof AllowedValueSubset) {
return 1;
}

return strcasecmp(
Utils::toString($valueA),
Utils::toString($valueB)
);
});

if (0 === \count($allowed)) {
$allowed = null;
}
}

return $allowed;
}

protected function initialize(InputInterface $input, OutputInterface $output): void
{
$output->getFormatter()->setStyle('url', new OutputFormatterStyle('blue'));
}
}
