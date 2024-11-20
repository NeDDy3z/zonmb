<?php










namespace Symfony\Component\Stopwatch;

use Symfony\Contracts\Service\ResetInterface;


class_exists(Section::class);






class Stopwatch implements ResetInterface
{



private $morePrecision;




private $sections;




private $activeSections;




public function __construct(bool $morePrecision = false)
{
$this->morePrecision = $morePrecision;
$this->reset();
}




public function getSections()
{
return $this->sections;
}








public function openSection(?string $id = null)
{
$current = end($this->activeSections);

if (null !== $id && null === $current->get($id)) {
throw new \LogicException(sprintf('The section "%s" has been started at an other level and cannot be opened.', $id));
}

$this->start('__section__.child', 'section');
$this->activeSections[] = $current->open($id);
$this->start('__section__');
}










public function stopSection(string $id)
{
$this->stop('__section__');

if (1 == \count($this->activeSections)) {
throw new \LogicException('There is no started section to stop.');
}

$this->sections[$id] = array_pop($this->activeSections)->setId($id);
$this->stop('__section__.child');
}






public function start(string $name, ?string $category = null)
{
return end($this->activeSections)->startEvent($name, $category);
}






public function isStarted(string $name)
{
return end($this->activeSections)->isEventStarted($name);
}






public function stop(string $name)
{
return end($this->activeSections)->stopEvent($name);
}






public function lap(string $name)
{
return end($this->activeSections)->stopEvent($name)->start();
}






public function getEvent(string $name)
{
return end($this->activeSections)->getEvent($name);
}






public function getSectionEvents(string $id)
{
return isset($this->sections[$id]) ? $this->sections[$id]->getEvents() : [];
}




public function reset()
{
$this->sections = $this->activeSections = ['__root__' => new Section(null, $this->morePrecision)];
}
}
