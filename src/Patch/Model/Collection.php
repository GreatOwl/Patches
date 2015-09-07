<?php

namespace TallTree\Roots\Patch\Model;

use IteratorAggregate;
use ArrayIterator;

class Collection implements IteratorAggregate
{
    private $patches;

    /**
     * @param Patch[] $patches
     */
    public function __construct(array $patches)
    {
        $this->patches = $patches;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->patches);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->patches);
    }

    /**
     * @param callable $filter
     * @return Collection
     */
    public function findAll(callable $filter)
    {
        $matches = array_filter($this->patches, $filter);

        return new static($matches);
    }

    /**
     * @param callable $filter
     * @return Patch|null
     */
    public function find(callable $filter)
    {
        $matches = $this->findAll($filter);
        $matches = $matches->getIterator();
        if ($matches->offsetExists(0)) {
            return $matches->offsetGet(0);
        }
        return null;
    }
}
