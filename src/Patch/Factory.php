<?php
namespace TallTree\Roots\Patch;

use TallTree\Roots\Patch\Model\Patch;
use TallTree\Roots\Patch\Model\Collection;

class Factory
{
    /**
     * @param array $raw
     * @return Patch
     */
    public function createPatch(array $raw)
    {
        return new Patch($raw);
    }

    /**
     * @param array $patches
     * @return Collection
     */
    public function createCollection(array $patches)
    {
        return new Collection($patches);
    }
}
