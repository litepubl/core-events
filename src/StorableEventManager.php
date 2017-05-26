<?php
namespace litepubl\core\events;

use litepubl\core\storage\StorableInterface;
use litepubl\core\storage\StorableItemsTrait;

class StorableEventManager extends EventManager implements StorableInterface
{
    use StorableItemsTrait;
}
