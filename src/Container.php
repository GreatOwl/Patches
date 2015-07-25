<?php
namespace GreatOwl\Patches;


class Container
{
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->data[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function dump()
    {
        return $this->data;
    }
}
