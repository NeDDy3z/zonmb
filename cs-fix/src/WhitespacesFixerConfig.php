<?php

declare(strict_types=1);











namespace PhpCsFixer;




final class WhitespacesFixerConfig
{
private string $indent;

private string $lineEnding;

public function __construct(string $indent = '    ', string $lineEnding = "\n")
{
if (!\in_array($indent, ['  ', '    ', "\t"], true)) {
throw new \InvalidArgumentException('Invalid "indent" param, expected tab or two or four spaces.');
}

if (!\in_array($lineEnding, ["\n", "\r\n"], true)) {
throw new \InvalidArgumentException('Invalid "lineEnding" param, expected "\n" or "\r\n".');
}

$this->indent = $indent;
$this->lineEnding = $lineEnding;
}

public function getIndent(): string
{
return $this->indent;
}

public function getLineEnding(): string
{
return $this->lineEnding;
}
}
