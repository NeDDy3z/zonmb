<?php

declare(strict_types=1);











namespace PhpCsFixer;

use Symfony\Component\Finder\Finder as BaseFinder;





class Finder extends BaseFinder
{
public function __construct()
{
parent::__construct();

$this
->files()
->name('/\.php$/')
->exclude('vendor')
;
}
}
