<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner\Parallel;






final class ParallelAction
{

public const RUNNER_REQUEST_ANALYSIS = 'requestAnalysis';
public const RUNNER_THANK_YOU = 'thankYou';


public const WORKER_ERROR_REPORT = 'errorReport';
public const WORKER_GET_FILE_CHUNK = 'getFileChunk';
public const WORKER_HELLO = 'hello';
public const WORKER_RESULT = 'result';

private function __construct() {}
}
