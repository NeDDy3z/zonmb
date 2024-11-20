<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Output\Progress;

use PhpCsFixer\Console\Output\OutputContext;




final class ProgressOutputFactory
{



private static array $outputTypeMap = [
ProgressOutputType::NONE => NullOutput::class,
ProgressOutputType::DOTS => DotsOutput::class,
ProgressOutputType::BAR => PercentageBarOutput::class,
];

public function create(string $outputType, OutputContext $context): ProgressOutputInterface
{
if (null === $context->getOutput()) {
$outputType = ProgressOutputType::NONE;
}

if (!$this->isBuiltInType($outputType)) {
throw new \InvalidArgumentException(
\sprintf(
'Something went wrong, "%s" output type is not supported',
$outputType
)
);
}

return new self::$outputTypeMap[$outputType]($context);
}

private function isBuiltInType(string $outputType): bool
{
return \in_array($outputType, ProgressOutputType::all(), true);
}
}
