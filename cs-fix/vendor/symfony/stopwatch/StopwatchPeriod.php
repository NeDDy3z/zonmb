<?php










namespace Symfony\Component\Stopwatch;






class StopwatchPeriod
{
private $start;
private $end;
private $memory;






public function __construct($start, $end, bool $morePrecision = false)
{
$this->start = $morePrecision ? (float) $start : (int) $start;
$this->end = $morePrecision ? (float) $end : (int) $end;
$this->memory = memory_get_usage(true);
}






public function getStartTime()
{
return $this->start;
}






public function getEndTime()
{
return $this->end;
}






public function getDuration()
{
return $this->end - $this->start;
}






public function getMemory()
{
return $this->memory;
}

public function __toString(): string
{
return sprintf('%.2F MiB - %d ms', $this->getMemory() / 1024 / 1024, $this->getDuration());
}
}
