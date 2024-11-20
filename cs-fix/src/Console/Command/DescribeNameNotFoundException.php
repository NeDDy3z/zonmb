<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\Command;




final class DescribeNameNotFoundException extends \InvalidArgumentException
{
private string $name;




private string $type;

public function __construct(string $name, string $type)
{
$this->name = $name;
$this->type = $type;

parent::__construct();
}

public function getName(): string
{
return $this->name;
}

public function getType(): string
{
return $this->type;
}
}
