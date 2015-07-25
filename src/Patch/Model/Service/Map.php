<?php
namespace GreatOwl\Patches\Patch\Model\Service;

use GreatOwl\Patches\Patch\Model\Patch;

interface Map
{

    /**
     * @param string $table
     * @return Patch
     */
    public function getPatches($table);

    /**
     * @param Patch $patch
     * @return void
     */
    public function applyPatch(Patch $patch);

    /**
     * @param Patch $originalPatch
     * @param Patch $newPatch
     * @return void
     */
    public function updatePatch(Patch $originalPatch, Patch $newPatch);
}
