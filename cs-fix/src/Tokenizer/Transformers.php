<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;








final class Transformers
{





private array $items = [];




private function __construct()
{
$this->registerBuiltInTransformers();

usort($this->items, static fn (TransformerInterface $a, TransformerInterface $b): int => $b->getPriority() <=> $a->getPriority());
}

public static function createSingleton(): self
{
static $instance = null;

if (!$instance) {
$instance = new self();
}

return $instance;
}






public function transform(Tokens $tokens): void
{
foreach ($this->items as $transformer) {
foreach ($tokens as $index => $token) {
$transformer->process($tokens, $token, $index);
}
}
}




private function registerTransformer(TransformerInterface $transformer): void
{
if (\PHP_VERSION_ID >= $transformer->getRequiredPhpVersionId()) {
$this->items[] = $transformer;
}
}

private function registerBuiltInTransformers(): void
{
static $registered = false;

if ($registered) {
return;
}

$registered = true;

foreach ($this->findBuiltInTransformers() as $transformer) {
$this->registerTransformer($transformer);
}
}




private function findBuiltInTransformers(): iterable
{

foreach (Finder::create()->files()->in(__DIR__.'/Transformer') as $file) {
$relativeNamespace = $file->getRelativePath();
$class = __NAMESPACE__.'\Transformer\\'.('' !== $relativeNamespace ? $relativeNamespace.'\\' : '').$file->getBasename('.php');

yield new $class();
}
}
}
