<?php
namespace litepubl\core\events;

use Psr\Container\ContainerInterface;
use litepubl\core\logmanager\LogManagerInterface;
use litepubl\core\storage\Storable;

class EventManager extends Callbacks implements Storable
{
    protected $names;

    public function __construct(ContainerInterface $container, $target)
    {
        $this->container = $container;
        $this->target = $target;
        $this->items = [];
        $this->names = [];
    }

    protected function canAttach(string $event, callable $calback): bool
    {
        $evemt = strtolower($event);
        return $this->hasEvent($event) && is_array($callback) && is_string($callback[0]);
    }

    public function attach(string $event, callable $callback, int $priority = 0): bool
    {
        if (parent::attach($event, $callback, $priority)) {
            $this->save();
                return true;
        }

        return false;
    }

    protected function removeItem(string $event, int $index)
    {
                    unset($this->items[$event][$i]);
        $this->sort();
        $this->save();
    }

    public function clearListeners(string $event)
    {
        if (isset($this->items[$event])) {
                unset($this->items[$event]);
            $this->save();
                return true;
        }

        return false;
    }

    protected function getListeners(string $event)
    {
        foreach ($this->items[$event] as $i => $item) {
            if (class_exists($item[0])) {
                        yield $i=> [$this->container->get($item[0]), $item[1]];
            } else {
                        $this->removeItem($event, $i);
                    $this->container->get(LogManagerInterface::class)->getLogger()->warning(sprintf('Class %s not exists when event triggered', $item[0]), [
                        'callback' => $item,
                        'event' => $event,
                        ]);
            }
        }
    }

    public function getBaseName(): string
    {
        return 'events';
    }

    public function getData(): array
    {
        return $this->items;
    }

    public function setData(array $data): void
    {
        $this->items = $data;
    }
}
