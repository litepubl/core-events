<?php

namespace litepubl\core\events;

class Callbacks
{
    protected $items = [];
    protected $target;

    public function __construct($target)
    {
        $this->target = $target;
        $this->items = [];
    }

    public function add(string $eventName, callable $callback, int $priority = 500): int
    {
        if (!isset($this->items[$eventName])) {
                $this->items[$eventName][$priority] = $callback;
        } else {
                Arr::append($this->items[$eventName], $priority, $callback);
        }

        return true;
    }

    public function delete(string $event, callable $callback): bool
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

    public function clear(string $event): bool
    {
        if (isset($this->items[$event])) {
                unset($this->items[$event]);
                return true;
        }

        return false;
    }

    public function getCount(string $event): int
    {
        return isset($this->items[$event]) ? count($this->items[$event]) : 0;
    }

    public function trigger($event, $params = []): array
    {
        if (is_object($event)) {
                $eventName = $event->getName();
        } else {
                $eventName = $event;
        }

        if (!$this->getCount($eventName)) {
                return $params;
        }

        if (is_string($event)) {
            $event = new Event($this->target, $eventName);
        }

                $event->setParams($params);
        foreach ($this->items[$eventName] as $i => $callback) {
            if ($event->isPropagationStopped()) {
                break;
            }

            try {
                        call_user_func_array($callback, [$event]);
                if ($event->once) {
                    $event->once = false;
                    unset($this->items[$eventName][$i]);
                }
            } catch (\Exception $e) {
                $this->getApp()->logException($e);
            }
        }

        return $event->getParams();
    }
}
