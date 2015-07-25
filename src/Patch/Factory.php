<?php
namespace GreatOwl\Patches\Patch;

use GreatOwl\Patches\Patch\Model\Patch;
use GreatOwl\Patches\Patch\Model\Collection;

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
