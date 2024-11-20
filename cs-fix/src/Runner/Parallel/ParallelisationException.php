<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner\Parallel;








final class ParallelisationException extends \RuntimeException
{
public static function forUnknownIdentifier(ProcessIdentifier $identifier): self
{
return new self('Unknown process identifier: '.$identifier->toString());
}
}
