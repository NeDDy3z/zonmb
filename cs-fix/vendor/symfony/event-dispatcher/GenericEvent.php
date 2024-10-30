<?php










namespace Symfony\Component\EventDispatcher;

use Symfony\Contracts\EventDispatcher\Event;

/**
@implements
@implements






*/
class GenericEvent extends Event implements \ArrayAccess, \IteratorAggregate
{
protected $subject;
protected $arguments;







public function __construct($subject = null, array $arguments = [])
{
$this->subject = $subject;
$this->arguments = $arguments;
}






public function getSubject()
{
return $this->subject;
}








public function getArgument(string $key)
{
if ($this->hasArgument($key)) {
return $this->arguments[$key];
}

throw new \InvalidArgumentException(sprintf('Argument "%s" not found.', $key));
}








public function setArgument(string $key, $value)
{
$this->arguments[$key] = $value;

return $this;
}






public function getArguments()
{
return $this->arguments;
}






public function setArguments(array $args = [])
{
$this->arguments = $args;

return $this;
}






public function hasArgument(string $key)
{
return \array_key_exists($key, $this->arguments);
}










#[\ReturnTypeWillChange]
public function offsetGet($key)
{
return $this->getArgument($key);
}









#[\ReturnTypeWillChange]
public function offsetSet($key, $value)
{
$this->setArgument($key, $value);
}








#[\ReturnTypeWillChange]
public function offsetUnset($key)
{
if ($this->hasArgument($key)) {
unset($this->arguments[$key]);
}
}








#[\ReturnTypeWillChange]
public function offsetExists($key)
{
return $this->hasArgument($key);
}






#[\ReturnTypeWillChange]
public function getIterator()
{
return new \ArrayIterator($this->arguments);
}
}
