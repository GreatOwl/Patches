<?php
namespace TallTree\Roots\Patch\Model\Service;

use TallTree\Roots\Patch\Model\Patch;

interface Map
{

    /**
     * @param string $table
     * @return array
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
