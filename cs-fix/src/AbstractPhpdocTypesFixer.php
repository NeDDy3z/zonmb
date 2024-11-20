<?php

declare(strict_types=1);











namespace PhpCsFixer;

use PhpCsFixer\DocBlock\Annotation;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\TypeExpression;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;








abstract class AbstractPhpdocTypesFixer extends AbstractFixer
{





protected array $tags;

public function __construct()
{
parent::__construct();

$this->tags = Annotation::getTagsWithTypes();
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isTokenKindFound(T_DOC_COMMENT);
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if (!$token->isGivenKind(T_DOC_COMMENT)) {
continue;
}

$doc = new DocBlock($token->getContent());
$annotations = $doc->getAnnotationsOfType($this->tags);

if (0 === \count($annotations)) {
continue;
}

foreach ($annotations as $annotation) {
$this->fixTypes($annotation);
}

$tokens[$index] = new Token([T_DOC_COMMENT, $doc->getContent()]);
}
}




abstract protected function normalize(string $type): string;








private function fixTypes(Annotation $annotation): void
{
$types = $annotation->getTypes();

$new = $this->normalizeTypes($types);

if ($types !== $new) {
$annotation->setTypes($new);
}
}






private function normalizeTypes(array $types): array
{
return array_map(
function (string $type): string {
$typeExpression = new TypeExpression($type, null, []);

$typeExpression->walkTypes(function (TypeExpression $type): void {
if (!$type->isUnionType()) {
$value = $this->normalize($type->toString());


\Closure::bind(static function () use ($type, $value): void {
$type->value = $value;
}, null, TypeExpression::class)();
}
});

return $typeExpression->toString();
},
$types
);
}
}
