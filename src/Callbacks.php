<?php

namespace litepubl\core\events;

use litepubl\core\logmanager\FactoryInterface as LogFactory;

class Callbacks implements EventManagerInterface
{
    protected $items;
    protected $target;
    protected $logFactory;

    public function __construct($target, LogFactory $logFactory)
    {
        $this->target = $target;
        $this->logFactory = $logFactory;
        $this->items = [];
    }

    public function attach(string $event, callable $callback, int $priority = 0): bool
    {
        if (is_array($callback) && is_string($callback[0])) {
                return false;
        }

        if (!isset($this->items[$event])) {
                $this->items[$event][$priority] = $callback;
        } else {
                Arr::append($this->items[$event], $priority, $callback);
        }

        return true;
    }

    public function detach(string $event, callable $callback): bool
    {
        if (isset($this->items[$event])) {
            foreach ($this->items[$event] as $i => $item) {
                if ($item == $callback) {
                    unset($this->items[$event][$i]);
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
                throw new TriggerException('Event must be  instance of EventInterface or string');
        }

        if ($this->hasListeners($eventName)) {
            if (!$eventInstance) {
                $eventInstance = new Event($eventName, $target, $params);
            } else {
                        $eventInstance->setParams($params);
            }

            foreach ($this->items[$eventName] as $i => $callback) {
                if ($event->isPropagationStopped()) {
                    break;
                }

                try {
                        $callback($eventInstance);
                    if ($eventInstance->isListenerToRemove()) {
                        $eventInstance->setListenerToRemove(false);
                        unset($this->items[$eventName][$i]);
                    }
                } catch (\Throwable $e) {
                    $this->logFactory->getLogManager()->logException($e, [
                    'callback' => $callback,
                    'event' => $eventInstance,
                    ]);
                }
            }
        }

        return $eventInstance ? $eventInstance->getParams() : $params;
    }
}
