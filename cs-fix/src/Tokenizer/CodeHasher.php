<?php

declare(strict_types=1);











namespace PhpCsFixer\Tokenizer;






final class CodeHasher
{
private function __construct()
{

}






public static function calculateCodeHash(string $code): string
{
return md5($code);
}
}
