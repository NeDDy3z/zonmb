<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Basic;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;






final class EncodingFixer extends AbstractFixer
{
private string $bom;

public function __construct()
{
parent::__construct();

$this->bom = pack('CCC', 0xEF, 0xBB, 0xBF);
}

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'PHP code MUST use only UTF-8 without BOM (remove BOM).',
[
new CodeSample(
$this->bom.'<?php

echo "Hello!";
'
),
]
);
}

public function getPriority(): int
{

return 100;
}

public function isCandidate(Tokens $tokens): bool
{
return true;
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
$content = $tokens[0]->getContent();

if (str_starts_with($content, $this->bom)) {
$newContent = substr($content, 3);

if ('' === $newContent) {
$tokens->clearAt(0);
} else {
$tokens[0] = new Token([$tokens[0]->getId(), $newContent]);
}
}
}
}
