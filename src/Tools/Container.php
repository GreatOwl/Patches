<?php
namespace TallTree\Roots\Tools;


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

    /**
     * @param $key
     * @param $value
     * @return Container
     */
    public function set($key, $value)
    {
        $data = $this->data;
        $data[$key] = $value;

        return new static($data);
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
