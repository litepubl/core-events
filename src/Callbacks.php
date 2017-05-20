<?php

namespace litepubl\core\events;

use Psr\Container\ContainerInterface;
use litepubl\core\logmanager\LogManagerInterface;

class Callbacks implements EventManagerInterface
{
    protected $container;
    protected $items;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->items = [];
    }

    protected function canAttach(string $event, callable $calback): bool
    {
        return !(is_array($callback) && is_string($callback[0]));
    }

    public function attach(string $event, callable $callback, int $priority = 0): bool
    {
        $event = strtolower($event);
        if (!$this->canAttach($event, $callback)) {
                return false;
        }

        if (!isset($this->items[$event])) {
                $this->items[$event][$priority] = $callback;
        } else {
            foreach ($this->items[$event] as $item) {
                if ($callback == $item) {
                    return false;
                }
            }

                Arr::append($this->items[$event], $priority, $callback);
        }

        return true;
    }

    protected function removeItem(string $event, int $index)
    {
                    unset($this->items[$event][$i]);
    }

    public function detach(string $event, callable $callback): bool
    {
        if (isset($this->items[$event])) {
            foreach ($this->items[$event] as $i => $item) {
                if ($item == $callback) {
                    $this->removeItem($event, $i);
                    return true;
                }
            }
        }

        return false;
    }

    public function clearListeners(string $event)
    {
        if (isset($this->items[$event])) {
                unset($this->items[$event]);
                return true;
        }

        return false;
    }

    public function hasListeners(string $event): bool
    {
        return isset($this->items[$event]) && count($this->items[$event]);
    }

//return \Iterable
    protected function getListeners(string $event)
    {
        return $this->items[$event];
    }

    protected function newEvent(string $event, $target, array $params): Event
    {
        return new Event($event, $target, $params);
    }

    public function trigger($event, $target = null, $argv = [])
    {
        if (is_string($event)) {
                $eventName = $event;
                $eventInstance = null;
        } elseif (is_object($event) && ($event instanceof EventInterface)) {
                $eventName = $event->getname();
                $eventInstance = $event;
        } else {
                throw new TriggerException('Event must be  instance of EventInterface or string');
        }

        if ($this->hasListeners($eventName)) {
            if (!$eventInstance) {
                $eventInstance = $this->newEvent($eventName, $target, $params);
            }

            $items = $this->getListeners($eventName);
            foreach ($items as $i => $callback) {
                if ($event->isPropagationStopped()) {
                    break;
                }

                try {
                        $callback($eventInstance);
                    if ($eventInstance->isListenerToRemove()) {
                        $eventInstance->setListenerToRemove(false);
                        $this->removeItem($eventName, $i);
                    }
                } catch (\Throwable $e) {
                    $this->removeItem($eventName, $i);
                    $this->container->get(logManagerInterface::class)->logException($e, [
                    'callback' => $callback,
                    'event' => $eventInstance,
                    ]);
                }
            }
        }

        return $eventInstance ? $eventInstance->getParams() : $params;
    }
}
