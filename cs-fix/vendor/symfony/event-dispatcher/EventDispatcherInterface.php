<?php










namespace Symfony\Component\EventDispatcher;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;








interface EventDispatcherInterface extends ContractsEventDispatcherInterface
{






public function addListener(string $eventName, callable $listener, int $priority = 0);







public function addSubscriber(EventSubscriberInterface $subscriber);




public function removeListener(string $eventName, callable $listener);

public function removeSubscriber(EventSubscriberInterface $subscriber);






public function getListeners(?string $eventName = null);








public function getListenerPriority(string $eventName, callable $listener);






public function hasListeners(?string $eventName = null);
}
