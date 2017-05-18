<?php

namespace litepubl\core\events;

class Events extends Data
{
    use EventsTrait;

    protected $map;

    public function __construct()
    {
        if (!is_array($this->map)) {
            $this->map = [];
        }

        parent::__construct();

        $this->assignmap();
        $this->load();
    }

    public function assignMap()
    {
        foreach ($this->map as $propname => $key) {
            $this->$propname = & $this->data[$key];
        }
    }

    public function afterLoad()
    {
        $this->assignMap();
        parent::afterload();
    }

    protected function addMap(string $name, $value)
    {
        $this->map[$name] = $name;
        $this->data[$name] = $value;
        $this->$name = & $this->data[$name];
    }

    public function free()
    {
        parent::free();
        unset($this->getApp()->classes->instances[get_class($this) ]);
    }
}
