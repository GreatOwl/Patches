<?php

namespace TallTree\Roots\Patch\Model;

use IteratorAggregate;
use ArrayIterator;

class Collection implements IteratorAggregate
{
    private $patches;
    private $table;
    private $stored = false;

    /**
     * @param Patch[] $patches
     */
    public function __construct(array $patches)
    {
        $this->patches = $patches;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->patches);
    }

    public function count()
    {
        return count($this->patches);
    }

    public function diff(Collection $patches)
    {
        /** @var Patch $patch */
        foreach ($patches as $patch) {
            $key = $this->matchPatch($patch);
            if (!is_null($key)) {
                $this->remove($key);
            }
        }

        $this->patches = array_values($this->patches);
    }

    public function remove($key)
    {
        if (array_key_exists($key, $this->patches)) {
            unset($this->patches[$key]);
        }
    }

    public function matchPatch(Patch $patch)
    {
        foreach ($this->patches as $key => $localPatch) {
            //This should be the case if both patches have the same history.
            if ($patch->getPatch() === $localPatch->getPatch() && $patch->getQuery() == $localPatch->getQuery()) {
                return $key;
            }
            //This should be the case if patches have a different history, or essentially do the same thing.
            if ($patch->getQuery() === $localPatch->getQuery()) {
                return $key;
            }
        }
        return false;
    }
}
