<?php
namespace TallTree\Roots\Patch;

use TallTree\Roots\Install\Model\Install;
use TallTree\Roots\Patch\Model\Collection;
use TallTree\Roots\Patch\Model\Patch;

class FilterFactory
{
    /**
     * Will filter out all patches that match between 2 collections.
     *
     * @param Collection $patched
     * @return \Closure
     */
    public function findUnmatched(Collection $patched)
    {
        return function(Patch $patch) use ($patched) {
            $patchStatement = $patch->getPatch();
            $patchQuery = $patch->getQuery();

            /** @var Patch $usedPatch */
            foreach ($patched as $usedPatch) {
                $usedPatchStatement = $usedPatch->getPatch();
                $usedPatchQuery = $usedPatch->getQuery();

                if ($patchStatement === $usedPatchStatement && $patchQuery == $usedPatchQuery) {
                    return false;
                }

                if ($patchQuery === $usedPatchStatement) {
                    return false;
                }
            }
            return true;
        };
    }

    /**
     * Will return all patches that are greater than the applied install provided.
     *
     * @param Install $originalInstall
     * @return \Closure
     */
    public function findAfterInstall(Install $originalInstall)
    {
        return function(Patch $patch) use ($originalInstall) {
            $patchNumber = $patch->getPatch();
            $installPatchNumber = $originalInstall->getPatch();

            if ($installPatchNumber == 0) {
                return $patchNumber >= $installPatchNumber;
            } else {
                return $patchNumber > $installPatchNumber;
            }
        };
    }

}
