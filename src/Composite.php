<?php

namespace litepubl\core\events;

class Composite implements EventManagerInterface
{
    protected $items;

    public function __construct(EventManagerInterface ... $items)
    {
        $this->items = $items;
    }

    public function add(EventManagerInterface $eventManager)
    {
        $this->items[] = $eventManager;
    }

    public function attach(string $event, callable $callback, int $priority = 0): bool
    {
        $result = false;
        foreach ($this->items as $item) {
            if ($item->attach($event, $callback, $priority)) {
                $result = true;
            }
        }

        return $result;
    }

    public function detach(string $event, callable $callback): bool
    {
        $result = false;
        foreach ($this->items as $item) {
            if ($item->detach($event, $callback)) {
                $result = true;
            }
        }

        return $result;
    }

    public function clearListeners(string $event)
    {
        foreach ($this->items as $item) {
                $item->clearListeners($event);
        }
    }

    public function hasListeners(string $event): bool
    {
        foreach ($this->items as $item) {
            if ($item->hasListeners($event)) {
                return true;
            }
        }

        return false;
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

        foreach ($this->items as $item) {
            if ($item->hasListeners($eventName)) {
                if (!$eventInstance) {
                    $eventInstance = new Event($eventName, $target, $params);
                }

                if ($eventInstance->isPropagationStopped()) {
                    break;
                }

                $item->trigger($eventInstance, $target, $params);
            }
        }

        return $eventInstance ? $eventInstance->getParams() : $params;
    }
}
