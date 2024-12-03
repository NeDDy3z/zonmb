<?php










namespace Symfony\Component\Stopwatch;






class StopwatchEvent
{



private $periods = [];




private $origin;




private $category;




private $morePrecision;




private $started = [];




private $name;









public function __construct(float $origin, ?string $category = null, bool $morePrecision = false, ?string $name = null)
{
$this->origin = $this->formatTime($origin);
$this->category = \is_string($category) ? $category : 'default';
$this->morePrecision = $morePrecision;
$this->name = $name ?? 'default';
}






public function getCategory()
{
return $this->category;
}






public function getOrigin()
{
return $this->origin;
}






public function start()
{
$this->started[] = $this->getNow();

return $this;
}








public function stop()
{
if (!\count($this->started)) {
throw new \LogicException('stop() called but start() has not been called before.');
}

$this->periods[] = new StopwatchPeriod(array_pop($this->started), $this->getNow(), $this->morePrecision);

return $this;
}






public function isStarted()
{
return !empty($this->started);
}






public function lap()
{
return $this->stop()->start();
}




public function ensureStopped()
{
while (\count($this->started)) {
$this->stop();
}
}






public function getPeriods()
{
return $this->periods;
}






public function getStartTime()
{
if (isset($this->periods[0])) {
return $this->periods[0]->getStartTime();
}

if ($this->started) {
return $this->started[0];
}

return 0;
}






public function getEndTime()
{
$count = \count($this->periods);

return $count ? $this->periods[$count - 1]->getEndTime() : 0;
}






public function getDuration()
{
$periods = $this->periods;
$left = \count($this->started);

for ($i = $left - 1; $i >= 0; --$i) {
$periods[] = new StopwatchPeriod($this->started[$i], $this->getNow(), $this->morePrecision);
}

$total = 0;
foreach ($periods as $period) {
$total += $period->getDuration();
}

return $total;
}






public function getMemory()
{
$memory = 0;
foreach ($this->periods as $period) {
if ($period->getMemory() > $memory) {
$memory = $period->getMemory();
}
}

return $memory;
}






protected function getNow()
{
return $this->formatTime(microtime(true) * 1000 - $this->origin);
}






private function formatTime(float $time): float
{
return round($time, 1);
}




public function getName(): string
{
return $this->name;
}

public function __toString(): string
{
return sprintf('%s/%s: %.2F MiB - %d ms', $this->getCategory(), $this->getName(), $this->getMemory() / 1024 / 1024, $this->getDuration());
}
}
