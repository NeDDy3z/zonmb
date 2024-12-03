<?php

declare(strict_types=1);











namespace PhpCsFixer\Linter;

use PhpCsFixer\FileReader;
use PhpCsFixer\Tokenizer\CodeHasher;
use PhpCsFixer\Tokenizer\Tokens;








final class TokenizerLinter implements LinterInterface
{
public function isAsync(): bool
{
return false;
}

public function lintFile(string $path): LintingResultInterface
{
return $this->lintSource(FileReader::createSingleton()->read($path));
}

public function lintSource(string $source): LintingResultInterface
{
try {




$codeHash = CodeHasher::calculateCodeHash($source);
Tokens::clearCache($codeHash);
Tokens::fromCode($source);

return new TokenizerLintingResult();
} catch (\CompileError|\ParseError $e) {
return new TokenizerLintingResult($e);
}
}
}
