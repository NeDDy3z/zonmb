<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\SelfUpdate;




interface GithubClientInterface
{



public function getTags(): array;
}
