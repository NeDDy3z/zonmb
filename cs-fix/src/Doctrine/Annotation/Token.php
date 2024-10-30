<?php

declare(strict_types=1);











namespace PhpCsFixer\Doctrine\Annotation;






final class Token
{
private int $type;

private string $content;

private int $position;





public function __construct(int $type = DocLexer::T_NONE, string $content = '', int $position = 0)
{
$this->type = $type;
$this->content = $content;
$this->position = $position;
}

public function getType(): int
{
return $this->type;
}

public function setType(int $type): void
{
$this->type = $type;
}

public function getContent(): string
{
return $this->content;
}

public function setContent(string $content): void
{
$this->content = $content;
}

public function getPosition(): int
{
return $this->position;
}






public function isType($types): bool
{
if (!\is_array($types)) {
$types = [$types];
}

return \in_array($this->getType(), $types, true);
}




public function clear(): void
{
$this->setContent('');
}
}
