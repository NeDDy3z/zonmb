<?php










namespace Symfony\Component\EventDispatcher;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

trigger_deprecation('symfony/event-dispatcher', '5.1', '%s is deprecated, use the event dispatcher without the proxy.', LegacyEventDispatcherProxy::class);








final class LegacyEventDispatcherProxy
{
public static function decorate(?EventDispatcherInterface $dispatcher): ?EventDispatcherInterface
{
return $dispatcher;
}
}
