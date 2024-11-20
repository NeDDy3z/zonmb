<?php

declare(strict_types=1);











namespace PhpCsFixer\Runner\Parallel;

use React\ChildProcess\Process as ReactProcess;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\WritableStreamInterface;











final class Process
{

private string $command;
private LoopInterface $loop;
private int $timeoutSeconds;


private ?ReactProcess $process = null;
private ?WritableStreamInterface $in = null;


private $stdErr;


private $stdOut;


private $onData;


private $onError;

private ?TimerInterface $timer = null;

public function __construct(string $command, LoopInterface $loop, int $timeoutSeconds)
{
$this->command = $command;
$this->loop = $loop;
$this->timeoutSeconds = $timeoutSeconds;
}






public function start(callable $onData, callable $onError, callable $onExit): void
{
$stdOut = tmpfile();
if (false === $stdOut) {
throw new ParallelisationException('Failed creating temp file for stdOut.');
}
$this->stdOut = $stdOut;

$stdErr = tmpfile();
if (false === $stdErr) {
throw new ParallelisationException('Failed creating temp file for stdErr.');
}
$this->stdErr = $stdErr;

$this->onData = $onData;
$this->onError = $onError;

$this->process = new ReactProcess($this->command, null, null, [
1 => $this->stdOut,
2 => $this->stdErr,
]);
$this->process->start($this->loop);
$this->process->on('exit', function ($exitCode) use ($onExit): void {
$this->cancelTimer();

$output = '';
rewind($this->stdOut);
$stdOut = stream_get_contents($this->stdOut);
if (\is_string($stdOut)) {
$output .= $stdOut;
}

rewind($this->stdErr);
$stdErr = stream_get_contents($this->stdErr);
if (\is_string($stdErr)) {
$output .= $stdErr;
}

$onExit($exitCode, $output);

fclose($this->stdOut);
fclose($this->stdErr);
});
}






public function request(array $data): void
{
$this->cancelTimer(); 

if (null === $this->in) {
throw new ParallelisationException(
'Process not connected with parallelisation operator, ensure `bindConnection()` was called'
);
}

$this->in->write($data);
$this->timer = $this->loop->addTimer($this->timeoutSeconds, function (): void {
($this->onError)(
new \Exception(
\sprintf(
'Child process timed out after %d seconds. Try making it longer using `ParallelConfig`.',
$this->timeoutSeconds
)
)
);
});
}

public function quit(): void
{
$this->cancelTimer();
if (null === $this->process || !$this->process->isRunning()) {
return;
}

foreach ($this->process->pipes as $pipe) {
$pipe->close();
}

if (null === $this->in) {
return;
}

$this->in->end();
}

public function bindConnection(ReadableStreamInterface $out, WritableStreamInterface $in): void
{
$this->in = $in;

$in->on('error', function (\Throwable $error): void {
($this->onError)($error);
});

$out->on('data', function (array $json): void {
$this->cancelTimer();


($this->onData)($json);
});
$out->on('error', function (\Throwable $error): void {
($this->onError)($error);
});
}

private function cancelTimer(): void
{
if (null === $this->timer) {
return;
}

$this->loop->cancelTimer($this->timer);
$this->timer = null;
}
}
