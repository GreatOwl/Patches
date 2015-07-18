<?php

namespace GreatOwl\Patches;


class Container
{
    private $data;

    private $original;

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->original = $data;
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

    public function reset($key = null)
    {
        if (is_null($key)) {
            $this->data = $this->original;
        } elseif (array_key_exists($key, $this->original)) {
            $this->data[$key] =  $this->original[$key];
        }
    }

    public function clear($key = null)
    {
        if (is_null($key)) {
            $this->data = [];
        } elseif ($this->has($key)) {
            $this->data[$key] = $key;
        }
    }

    public function changes()
    {
        return array_diff_assoc($this->data, $this->original);
    }

    public function hasChanged($key = null)
    {
        $changes = $this->changes();
        if (is_null($key)) {
            return count($changes) > 0;
        } else {
            return array_key_exists($key, $changes);
        }
    }

    public function hasAnyChanged(array $keys = [])
    {
        foreach ($keys as $key) {
            if ($this->hasChanged($key)) {
                return true;
            }
        }

        return false;
    }
}
