<?php

declare(strict_types=1);











namespace PhpCsFixer\Fixer\Casing;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;






final class LowercaseKeywordsFixer extends AbstractFixer
{



private static array $excludedTokens = [T_HALT_COMPILER];

public function getDefinition(): FixerDefinitionInterface
{
return new FixerDefinition(
'PHP keywords MUST be in lower case.',
[
new CodeSample(
'<?php
    FOREACH($a AS $B) {
        TRY {
            NEW $C($a, ISSET($B));
            WHILE($B) {
                INCLUDE "test.php";
            }
        } CATCH(\Exception $e) {
            EXIT(1);
        }
    }
'
),
]
);
}

public function isCandidate(Tokens $tokens): bool
{
return $tokens->isAnyTokenKindsFound(Token::getKeywords());
}

protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
{
foreach ($tokens as $index => $token) {
if ($token->isKeyword() && !$token->isGivenKind(self::$excludedTokens)) {
$tokens[$index] = new Token([$token->getId(), strtolower($token->getContent())]);
}
}
}
}
