<?php

namespace litepubl\core\events;

class Composite implements EventManagerInterface
{
    protected $items;

    public function __construct(EventManagerInterface ... $items)
    {
        $this->items = $items;
    }
    public function attach(string $event, callable $callback, int $priority = 0): bool
    {
        foreach ($this->items as $item) {
            if ($item->attach($event, $callback, $priority)) {
                return true;
            }
        }

        return false;
    }

    public function detach(string $event, callable $callback): bool
    {
        foreach ($this->items as $item) {
            if ($item->detach($event, $callback)) {
                return true;
            }
        }

        return false;
    }

    public function clearListeners(string $event)
    {
        foreach ($this->items as $item) {
                $item->clearListeners($event);
        }
    }

    public function trigger($event, $target = null, $argv = [])
    {
        if (is_string($event)) {
                $eventName = $event;
                $eventInstance = null;
        } elseif (is_object($event) && ($event instanceof EventInterface)) {
                $eventName = $event->getname();
                $eventInstance = $event;
                $eventInstance->setParams($params);
        } else {
                throw new Exception();
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
