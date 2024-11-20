<?php










namespace Symfony\Component\Stopwatch;






class Section
{



private $events = [];




private $origin;




private $morePrecision;




private $id;




private $children = [];





public function __construct(?float $origin = null, bool $morePrecision = false)
{
$this->origin = $origin;
$this->morePrecision = $morePrecision;
}






public function get(string $id)
{
foreach ($this->children as $child) {
if ($id === $child->getId()) {
return $child;
}
}

return null;
}








public function open(?string $id)
{
if (null === $id || null === $session = $this->get($id)) {
$session = $this->children[] = new self(microtime(true) * 1000, $this->morePrecision);
}

return $session;
}




public function getId()
{
return $this->id;
}






public function setId(string $id)
{
$this->id = $id;

return $this;
}






public function startEvent(string $name, ?string $category)
{
if (!isset($this->events[$name])) {
$this->events[$name] = new StopwatchEvent($this->origin ?: microtime(true) * 1000, $category, $this->morePrecision, $name);
}

return $this->events[$name]->start();
}






public function isEventStarted(string $name)
{
return isset($this->events[$name]) && $this->events[$name]->isStarted();
}








public function stopEvent(string $name)
{
if (!isset($this->events[$name])) {
throw new \LogicException(sprintf('Event "%s" is not started.', $name));
}

return $this->events[$name]->stop();
}








public function lap(string $name)
{
return $this->stopEvent($name)->start();
}








public function getEvent(string $name)
{
if (!isset($this->events[$name])) {
throw new \LogicException(sprintf('Event "%s" is not known.', $name));
}

return $this->events[$name];
}






public function getEvents()
{
return $this->events;
}
}
